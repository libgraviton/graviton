<?php
/**
 * QueryService
 */
namespace Graviton\RestBundle\Service;

use Doctrine\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\DocumentRepository;
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
     * @var integer
     */
    private $paginationDefaultLimit;

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
     * @param integer          $paginationDefaultLimit default pagination limit
     */
    public function __construct(
        VisitorInterface $visitor,
        $paginationDefaultLimit
    ) {
        $this->visitor = $visitor;
        $this->paginationDefaultLimit = intval($paginationDefaultLimit);
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
        $this->request = &$request;
        $this->repository = $repository;
        $this->queryBuilder = $repository->createQueryBuilder();

        $this->applyRqlQuery();

        if (is_null($this->getDocumentId())) {
            // get all action
            $query = $this->queryBuilder->getQuery();

            $records = array_values($query->execute()->toArray());
            $totalCount = $query->count();
            $numPages = (int) ceil($totalCount / $this->getPaginationPageSize());
            $page = (int) ceil($this->getPaginationSkip() / $this->getPaginationPageSize()) + 1;
            if ($numPages > 1) {
                $request->attributes->set('paging', true);
                $request->attributes->set('page', $page);
                $request->attributes->set('numPages', $numPages);
                $request->attributes->set('startAt', $this->getPaginationSkip());
                $request->attributes->set('perPage', $this->getPaginationPageSize());
                $request->attributes->set('totalCount', $totalCount);
            }

            return $records;
        } else {
            $this->queryBuilder->field('id')->equals($this->getDocumentId());
            $records = $this->queryBuilder->getQuery()->getSingleResult();
        }

        return $records;
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
     * apply all stuff from the rql query (if any) to the local querybuilder
     *
     * @return void
     */
    private function applyRqlQuery()
    {
        /** @var Query $rqlQuery */
        $rqlQuery = $this->request->attributes->get('rqlQuery');

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

            $this->visitor->setBuilder($this->queryBuilder);
            $this->queryBuilder = $this->visitor->visit($rqlQuery);
        }

        if (is_null($this->getDocumentId())) {
            /*** default sort ***/
            if (!array_key_exists('sort', $this->queryBuilder->getQuery()->getQuery())) {
                $this->queryBuilder->sort('_id');
            }

            /*** pagination stuff ***/
            $this->queryBuilder->skip($this->getPaginationSkip());
            $this->queryBuilder->limit($this->getPaginationPageSize());
        }
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
