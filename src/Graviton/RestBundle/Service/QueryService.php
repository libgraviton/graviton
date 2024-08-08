<?php
/**
 * QueryService
 */
namespace Graviton\RestBundle\Service;

use Doctrine\ODM\MongoDB\Query\Builder;
use Graviton\DocumentBundle\Service\SolrQuery;
use Graviton\RestBundle\Event\ModelQueryEvent;
use Graviton\RestBundle\Exception\RqlOperatorNotAllowedException;
use Graviton\RestBundle\Model\DocumentModel;
use Graviton\Rql\Node\SearchNode;
use Graviton\Rql\Visitor\VisitorInterface;
use Graviton\RqlParser\AbstractNode;
use Graviton\RqlParser\Exception\SyntaxErrorException;
use Graviton\RqlParser\Node\LimitNode;
use Graviton\RqlParser\Node\Query\LogicalOperator\AndNode;
use Graviton\RqlParser\Query;
use Graviton\RqlParserBundle\Component\RequestParser;
use MongoDB\Driver\ReadPreference;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * class that deals with the Request and applies it to the query builder
 * in order to get the results needed
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
readonly class QueryService
{

    /**
     * @param LoggerInterface          $logger                         logger
     * @param RequestParser            $rqlRequestParser               request parser
     * @param VisitorInterface         $visitor                        visitor
     * @param integer                  $paginationDefaultLimit         default pagination limit
     * @param EventDispatcherInterface $eventDispatcher                event dispatcher
     * @param SolrQuery                $solrQuery                      solr query
     * @param bool                     $mongoDbCounterEnabled          mongoDbCounterEnabled
     * @param ?string                  $enableMongoDbCounterHeaderName enableMongoDbCounterHeaderName
     */
    public function __construct(
        private LoggerInterface $logger,
        private RequestParser $rqlRequestParser,
        private VisitorInterface $visitor,
        private int $paginationDefaultLimit,
        private EventDispatcherInterface $eventDispatcher,
        private SolrQuery $solrQuery,
        private bool $mongoDbCounterEnabled,
        private ?string $enableMongoDbCounterHeaderName
    ) {
    }

    /**
     * public function that returns an array of records (or just one record if that's requested)
     * based on the Request passed to it.
     * sets all necessary stuff on the querybuilder and the request
     *
     * @param Request       $request request
     * @param DocumentModel $model   model
     *
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     *
     * @return array|null|object either array of records or the record
     *
     */
    public function getWithRequest(Request $request, DocumentModel $model)
    {
        $returnValue = null;
        $repository = $model->getRepository();

        // if id is *not* empty, then a single document is requested!
        $singleDocumentRequest = !empty($this->getDocumentId($request));

        $queryBuilder = $this->applyRqlQuery($request, $model, $singleDocumentRequest);

        if ($model->getRuntimeDefinition()->isPreferredReadFromSecondary()) {
            $readPreference = new ReadPreference(ReadPreference::RP_SECONDARY_PREFERRED);
        } else {
            $readPreference = new ReadPreference(ReadPreference::RP_PRIMARY);
        }

        // dispatch our event if normal builder
        if ($queryBuilder instanceof Builder) {
            $queryBuilder = $this
                ->executeQueryEvent($queryBuilder)
                ->setReadPreference($readPreference);
        }

        if ($queryBuilder instanceof \Doctrine\ODM\MongoDB\Aggregation\Builder) {
            /**
             * this is only the case when queryBuilder was overridden, most likely via a PostEvent
             * in the rql parsing phase.
             */
            $queryBuilder->hydrate($repository->getClassName());

            $this->logger->info(
                'QueryService: Aggregate query',
                [
                    'readPref' => $readPreference->getModeString()
                ]
            );

            $records = array_values(
                $queryBuilder->getAggregation(['readPreference' => $readPreference])->getIterator()->toArray()
            );

            $request->attributes->set('recordCount', count($records));

            $returnValue = $records;
        } elseif (!$singleDocumentRequest) {
            /**
             * this is or the "all" action -> multiple documents returned
             */
            $shouldCalculateTotal = (
                $this->mongoDbCounterEnabled || $request->headers->has($this->enableMongoDbCounterHeaderName)
            );
            $totalCount = null;

            if ($shouldCalculateTotal) {
                // count queryBuilder
                $countQueryBuilder = clone $queryBuilder;
                $countQueryBuilder->count()
                                  ->limit(0)
                                  ->skip(0);
                $totalCount = $countQueryBuilder->getQuery()
                                                ->execute();
            }

            /*
             * PAGINATION TRICK:
             * - increase limit by 1
             * - see if we get that one more -> if so, we know that there's a "next" page..
             * - only return the original limit amount of records.
             */

            $mainQueryParts = $queryBuilder->getQuery()->getQuery();
            if (!isset($mainQueryParts['limit'])) {
                $mainQueryParts['limit'] = $this->getPaginationPageSize($request);
            }

            // save original size!
            $originalPagesize = $mainQueryParts['limit'];
            // set one more on querybuilder!
            $queryBuilder->limit($originalPagesize + 1);

            $mainQuery = $queryBuilder->getQuery();

            $this->logger->info(
                'QueryService: allAction query',
                [
                    'q' => $mainQuery->getQuery(),
                    'totalCountEnabled' => $shouldCalculateTotal,
                    'totalCount' => $totalCount,
                    'readPref' => $readPreference->getModeString()
                ]
            );

            $records = array_values($mainQuery->execute()->toArray());

            // paging: one more or not?
            $hasNextPage = false;
            if (count($records) > $originalPagesize) {
                $hasNextPage = true;
                // remove the surplus one!
                array_pop($records);
            }

            if (!empty($totalCount)) {
                $request->attributes->set('totalCount', $totalCount);
            }
            $request->attributes->set('recordCount', count($records));
            $request->attributes->set('hasNextPage', $hasNextPage);

            $returnValue = $records;
        } else {
            /**
             * this is the "getAction" -> one document returned
             */
            $queryBuilder->field('id')->equals($this->getDocumentId($request));

            $this->logger->info(
                'QueryService: getAction query',
                ['q' => $queryBuilder->getQuery()->getQuery(), 'readPref' => $readPreference->getModeString()]
            );

            $query = $queryBuilder->getQuery();
            $records = array_values($query->execute()->toArray());

            if (is_array($records) && !empty($records) && is_object($records[0])) {
                $returnValue = $records[0];

                $request->attributes->set('totalCount', 1);
                $request->attributes->set('recordCount', 1);
            } else {
                $request->attributes->set('totalCount', 0);
                $request->attributes->set('recordCount', 0);
            }
        }

        // set attributes
        if (!is_null($returnValue)) {
            $page = (int) ceil($this->getPaginationSkip($request) / $this->getPaginationPageSize($request)) + 1;
            $request->attributes->set('page', $page);

            $request->attributes->set('startAt', $this->getPaginationSkip($request));
            $request->attributes->set('perPage', $this->getPaginationPageSize($request));

            if ($request->attributes->has('totalCount')) {
                $numPages = (int) ceil(
                    $request->attributes->get('totalCount') / $this->getPaginationPageSize($request)
                );
                $request->attributes->set('numPages', $numPages);
            }
        }

        return $returnValue;
    }

    /**
     * passes the query builder to any listeners that are subscribed to the ModelQueryEvent
     *
     * @param Builder $builder builder
     *
     * @return Builder builder
     */
    public function executeQueryEvent(Builder $builder)
    {
        $event = new ModelQueryEvent();
        $event->setQueryBuilder($builder);
        $event = $this->eventDispatcher->dispatch($event, ModelQueryEvent::NAME);
        return $event->getQueryBuilder();
    }

    /**
     * if a single document has been requested, this returns the document id. if it returns null,
     * then we return multiple documents
     *
     * @param Request $request request
     *
     * @return string|null either document id or null
     */
    private function getDocumentId(Request $request)
    {
        return $request->attributes->get('singleDocument', null);
    }

    /**
     * apply all stuff from the rql query (if any) to the local querybuilder
     *
     * @param Request       $request               request
     * @param DocumentModel $model                 model
     * @param bool          $singleDocumentRequest if single document is requested
     *
     * @return Builder|\Doctrine\ODM\MongoDB\Aggregation\Builder builder
     */
    private function applyRqlQuery(Request $request, DocumentModel $model, bool $singleDocumentRequest)
    {
        $rqlQuery = $this->getRqlQuery($request, $singleDocumentRequest);
        $queryBuilder = null;

        // Setting RQL Query
        if ($rqlQuery) {
            // set on request for Link header
            $request->attributes->set('rqlQuery', $rqlQuery);

            // Check if search and if this Repository have search indexes.
            if ($query = $rqlQuery->getQuery()) {
                if ($query instanceof AndNode) {
                    foreach ($query->getQueries() as $xq) {
                        if ($xq instanceof SearchNode && !$this->hasSearchIndex($model)) {
                            throw new \InvalidArgumentException('Search operation not supported on this endpoint');
                        }
                    }
                } elseif ($query instanceof SearchNode && !$this->hasSearchIndex($model)) {
                    throw new \InvalidArgumentException('Search operation not supported on this endpoint');
                }
            }

            $this->visitor->setRepository($model->getRepository());
            $queryBuilder = $this->visitor->visit($rqlQuery);
        }

        if (is_null($queryBuilder)) {
            $queryBuilder = $model->getRepository()->createQueryBuilder();
        }

        if (is_null($this->getDocumentId($request)) && $queryBuilder instanceof Builder) {
            $currentQuery = $queryBuilder->getQuery()->getQuery();

            /*** default sort ***/

            if (!array_key_exists('sort', $currentQuery)) {
                $queryBuilder->sort('_id');
            }

            /*** pagination stuff ***/
            if (!array_key_exists('limit', $currentQuery)) {
                $queryBuilder->skip($this->getPaginationSkip($request));
                $queryBuilder->limit($this->getPaginationPageSize($request));
            }
        }

        return $queryBuilder;
    }

    /**
     * returns the correct rql query for the request, including optional specified restrictions
     * in the service definition (via restrictionManager)
     *
     * @param Request $request               request
     * @param bool    $singleDocumentRequest if single document is requested
     *
     * @return Query|null the query
     */
    private function getRqlQuery(Request $request, bool $singleDocumentRequest) : ?Query
    {
        $res = $this->rqlRequestParser->parse($request);

        if (!$res->isHasRql()) {
            return null;
        }

        $query = $res->getRqlQuery();

        if ($singleDocumentRequest) {
            // complain that some rql operators are not supported on single GET (only getAll)
            foreach (['getQuery', 'getSort', 'getLimit'] as $method) {
                /** @var AbstractNode $node */
                $node = $query->$method();
                if ($node != null) {
                    throw new RqlOperatorNotAllowedException($node->getNodeName());
                }
            }
        }

        return $query;
    }

    /**
     * Check if collection has search indexes in DB
     *
     * @param DocumentModel $model model
     *
     * @return bool
     */
    private function hasSearchIndex(DocumentModel $model) : bool
    {
        if ($this->solrQuery->hasSolr($model->getEntityClass())) {
            return true;
        }

        $metadata = $model->getRepository()->getClassMetadata();
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
     * @param Request $request request
     *
     * @return int page size
     */
    private function getPaginationPageSize(Request $request) : int
    {
        $limitNode = $this->getPaginationLimitNode($request);

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
     * @param Request $request request
     *
     * @return int skip
     */
    private function getPaginationSkip(Request $request) : int
    {
        $limitNode = $this->getPaginationLimitNode($request);

        if ($limitNode) {
            return abs($limitNode->getOffset());
        }

        return 0;
    }

    /**
     * gets the limit node
     *
     * @param Request $request request
     *
     * @return ?LimitNode limit node
     */
    private function getPaginationLimitNode(Request $request) : ?LimitNode
    {
        /** @var Query $rqlQuery */
        $rqlQuery = $request->attributes->get('rqlQuery');

        if ($rqlQuery instanceof Query && $rqlQuery->getLimit() instanceof LimitNode) {
            return $rqlQuery->getLimit();
        }

        return null;
    }
}
