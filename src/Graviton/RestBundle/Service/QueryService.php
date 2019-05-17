<?php
/**
 * QueryService
 */
namespace Graviton\RestBundle\Service;

use Doctrine\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Graviton\RestBundle\Restriction\Manager;
use Graviton\Rql\Node\SearchNode;
use Graviton\Rql\Visitor\VisitorInterface;
use Symfony\Component\HttpFoundation\Request;
use Xiag\Rql\Parser\Exception\SyntaxErrorException;
use Xiag\Rql\Parser\Node\LimitNode;
use Xiag\Rql\Parser\Node\Query\LogicOperator\AndNode;
use Xiag\Rql\Parser\Query;

/**
 * class that deals with the Request and applies it to the query builder
 * in order to get the results needed
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class QueryService
{

    /**
     * @var VisitorInterface
     */
    private $visitor;

    /**
     * @var Manager
     */
    private $restrictionManager;

    /**
     * @var integer
     */
    private $paginationDefaultLimit;

    /**
     * @var array
     */
    private $dataRestrictionMap;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Builder
     */
    private $queryBuilder;

    /**
     * @var DocumentRepository
     */
    private $repository;

    /**
     * @param VisitorInterface $visitor                visitor
     * @param Manager          $restrictionManager     restriction manager
     * @param integer          $paginationDefaultLimit default pagination limit
     * @param array            $dataRestrictionMap     data restriction configuration
     */
    public function __construct(
        VisitorInterface $visitor,
        Manager $restrictionManager,
        $paginationDefaultLimit,
        array $dataRestrictionMap
    ) {
        $this->visitor = $visitor;
        $this->restrictionManager = $restrictionManager;
        $this->paginationDefaultLimit = intval($paginationDefaultLimit);
        $this->setDataRestrictionMap($dataRestrictionMap);
    }

    /**
     * set DataRestrictionMap
     *
     * @param array $dataRestrictionMap dataRestrictionMap
     *
     * @return void
     */
    public function setDataRestrictionMap(?array $dataRestrictionMap)
    {
        if (!is_array($dataRestrictionMap)) {
            return;
        }

        foreach ($dataRestrictionMap as $headerName => $fieldName) {
            $valueParts = explode(':', $fieldName);
            if (count($valueParts) == 1) {
                $this->dataRestrictionMap[$headerName] = [
                    'type' => 'string',
                    'name' => $valueParts[0]
                ];
            } elseif (count($valueParts) == 2) {
                $this->dataRestrictionMap[$headerName] = [
                    'type' => $valueParts[0],
                    'name' => $valueParts[1]
                ];
            } else {
                throw new \LogicException("Wrong data restriction value as '${headerName}' '${fieldName}'");
            }
        }
    }

    /**
     * public function that returns an array of records (or just one record if that's requested)
     * based on the Request passed to it.
     * sets all necessary stuff on the querybuilder and the request
     *
     * @param Request            $request    request
     * @param DocumentRepository $repository repository
     *
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     *
     * @return array|null|object either array of records or the record
     *
     */
    public function getWithRequest(Request &$request, DocumentRepository $repository)
    {
        $returnValue = null;

        $this->request = &$request;
        $this->repository = $repository;
        $this->queryBuilder = $repository->createQueryBuilder();

        $this->applyRqlQuery();

        if ($this->queryBuilder instanceof \Doctrine\ODM\MongoDB\Aggregation\Builder) {
            /**
             * this is only the case when queryBuilder was overridden, most likely via a PostEvent
             * in the rql parsing phase.
             */
            $this->queryBuilder->hydrate($repository->getClassName());

            $records = array_values($this->queryBuilder->execute()->toArray());
            $request->attributes->set('recordCount', count($records));

            $returnValue = $records;
        } elseif (is_null($this->getDocumentId())) {
            /**
             * this is or the "all" action -> multiple documents returned
             */

            $this->applyDataRestrictions();

            $query = $this->queryBuilder->getQuery();
            $records = array_values($query->execute()->toArray());

            $request->attributes->set('totalCount', $query->count());
            $request->attributes->set('recordCount', count($records));

            $returnValue = $records;
        } else {
            /**
             * this is the "getAction" -> one document returned
             */
            $this->queryBuilder->field('id')->equals($this->getDocumentId());

            $this->applyDataRestrictions();

            $query = $this->queryBuilder->getQuery();
            $records = array_values($query->execute()->toArray());

            if (is_array($records) && !empty($records) && is_object($records[0])) {
                $returnValue = $records[0];
            }
        }

        // need to set paging information?
        if (!is_null($returnValue) && $request->attributes->has('totalCount')) {
            $numPages = (int) ceil($request->attributes->get('totalCount') / $this->getPaginationPageSize());
            $page = (int) ceil($this->getPaginationSkip() / $this->getPaginationPageSize()) + 1;
            if ($numPages > 1) {
                $request->attributes->set('paging', true);
                $request->attributes->set('page', $page);
                $request->attributes->set('numPages', $numPages);
                $request->attributes->set('startAt', $this->getPaginationSkip());
                $request->attributes->set('perPage', $this->getPaginationPageSize());
            }
        }

        return $returnValue;
    }

    /**
     * if a single document has been requested, this returns the document id. if it returns null,
     * then we return multiple documents
     *
     * @return string|null either document id or null
     */
    private function getDocumentId()
    {
        return $this->request->attributes->get('singleDocument', null);
    }

    /**
     * apply configured data restrictions on select queries
     *
     * @return void
     */
    private function applyDataRestrictions()
    {
        if (!is_array($this->dataRestrictionMap) || empty($this->dataRestrictionMap)) {
            return null;
        }

        foreach ($this->dataRestrictionMap as $headerName => $fieldSpec) {
            $headerValue = $this->request->headers->get($headerName, null);

            if ($headerValue == null) {
                continue;
            }

            if ($fieldSpec['type'] == 'int') {
                $headerValue = (int) $headerValue;
            }

            $this->queryBuilder->addAnd(
                $this->queryBuilder->expr()->field($fieldSpec['name'])->in([null, $headerValue])
            );
        }
    }

    /**
     * @param Request $request request
     * @param object  $entity  entity
     *
     * @return object altered object
     */
    public function applyDataRestrictionsOnInsert(Request $request, $entity)
    {
        if (!is_array($this->dataRestrictionMap) ||
            empty($this->dataRestrictionMap) ||
            !($entity instanceof \ArrayAccess)
        ) {
            return $entity;
        }

        foreach ($this->dataRestrictionMap as $headerName => $fieldSpec) {
            $headerValue = $request->headers->get($headerName, null);
            if (!is_null($headerValue)) {
                $entity[$fieldSpec['name']] = $headerValue;
            }
        }

        return $entity;
    }

    /**
     * apply all stuff from the rql query (if any) to the local querybuilder
     *
     * @return void
     */
    private function applyRqlQuery()
    {
        $rqlQuery = $this->getRqlQuery();

        // Setting RQL Query
        if ($rqlQuery) {
            // Check if search and if this Repository have search indexes.
            if ($query = $rqlQuery->getQuery()) {
                if ($query instanceof AndNode) {
                    foreach ($query->getQueries() as $xq) {
                        if ($xq instanceof SearchNode && !$this->hasSearchIndex()) {
                            throw new \InvalidArgumentException('Search operation not supported on this endpoint');
                        }
                    }
                } elseif ($query instanceof SearchNode && !$this->hasSearchIndex()) {
                    throw new \InvalidArgumentException('Search operation not supported on this endpoint');
                }
            }

            $this->visitor->setRepository($this->repository);
            $this->queryBuilder = $this->visitor->visit($rqlQuery);
        }

        if (is_null($this->getDocumentId()) && $this->queryBuilder instanceof Builder) {

            /*** default sort ***/
            if (!array_key_exists('sort', $this->queryBuilder->getQuery()->getQuery())) {
                $this->queryBuilder->sort('_id');
            }

            /*** pagination stuff ***/
            if (!array_key_exists('limit', $this->queryBuilder->getQuery()->getQuery())) {
                $this->queryBuilder->skip($this->getPaginationSkip());
                $this->queryBuilder->limit($this->getPaginationPageSize());
            }
        }
    }

    /**
     * returns the correct rql query for the request, including optional specified restrictions
     * in the service definition (via restrictionManager)
     *
     * @return Query the query
     */
    private function getRqlQuery()
    {
        /** @var Query $rqlQuery */
        $rqlQuery = $this->request->attributes->get('rqlQuery', false);

        // apply field restrictions as specified in service definition
        $restrictionNode = $this->restrictionManager->handle($this->repository);
        if ($restrictionNode) {
            if (!$rqlQuery instanceof Query) {
                $rqlQuery = new Query();
            }

            $query = $rqlQuery->getQuery();
            if (is_null($query)) {
                // only our query
                $query = $restrictionNode;
            } else {
                // we have an existing query
                $query = new AndNode(
                    [
                        $query,
                        $restrictionNode
                    ]
                );
            }

            $rqlQuery->setQuery($query);
        }

        return $rqlQuery;
    }

    /**
     * Check if collection has search indexes in DB
     *
     * @return bool
     */
    private function hasSearchIndex()
    {
        $metadata = $this->repository->getClassMetadata();
        $indexes = $metadata->getIndexes();
        if (empty($indexes)) {
            return false;
        }

        $text = array_filter(
            $indexes,
            function ($index) {
                if (isset($index['keys'])) {
                    $hasText = false;
                    foreach ($index['keys'] as $name => $direction) {
                        if ($direction == 'text') {
                            $hasText = true;
                        }
                    }
                    return $hasText;
                }
            }
        );

        return !empty($text);
    }

    /**
     * get the pagination page size
     *
     * @return int page size
     */
    private function getPaginationPageSize()
    {
        $limitNode = $this->getPaginationLimitNode();

        if ($limitNode) {
            $limit = $limitNode->getLimit();

            if ($limit < 1) {
                throw new SyntaxErrorException('invalid limit in rql');
            }

            return $limit;
        }

        return $this->paginationDefaultLimit;
    }

    /**
     * gets the pagination skip
     *
     * @return int skip
     */
    private function getPaginationSkip()
    {
        $limitNode = $this->getPaginationLimitNode();

        if ($limitNode) {
            return abs($limitNode->getOffset());
        }

        return 0;
    }

    /**
     * gets the limit node
     *
     * @return bool|LimitNode the node or false
     */
    private function getPaginationLimitNode()
    {
        /** @var Query $rqlQuery */
        $rqlQuery = $this->request->attributes->get('rqlQuery');

        if ($rqlQuery instanceof Query && $rqlQuery->getLimit() instanceof LimitNode) {
            return $rqlQuery->getLimit();
        }

        return false;
    }
}
