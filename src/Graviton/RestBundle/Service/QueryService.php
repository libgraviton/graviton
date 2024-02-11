<?php
/**
 * QueryService
 */
namespace Graviton\RestBundle\Service;

use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Graviton\DocumentBundle\Service\SolrQuery;
use Graviton\ExceptionBundle\Exception\RqlOperatorNotAllowedException;
use Graviton\RestBundle\Event\ModelQueryEvent;
use Graviton\Rql\Node\SearchNode;
use Graviton\Rql\Visitor\VisitorInterface;
use Graviton\RqlParser\AbstractNode;
use Graviton\RqlParserBundle\Component\RequestParser;
use MongoDB\Driver\ReadPreference;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Graviton\RqlParser\Exception\SyntaxErrorException;
use Graviton\RqlParser\Node\LimitNode;
use Graviton\RqlParser\Node\Query\LogicalOperator\AndNode;
use Graviton\RqlParser\Query;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RequestParser
     */
    private RequestParser $rqlRequestParser;

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
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    protected SolrQuery $solrQuery;

    /**
     * @var bool toggles if we should send readpref 'secondarypreferred'
     */
    private bool $isUseSecondary = false;

    private bool $mongoDbCounterEnabled;
    private ?string $enableMongoDbCounterHeaderName;

    /**
     * @param LoggerInterface          $logger                         logger
     * @param RequestParser            $requestParser                  request parser
     * @param VisitorInterface         $visitor                        visitor
     * @param integer                  $paginationDefaultLimit         default pagination limit
     * @param EventDispatcherInterface $eventDispatcher                event dispatcher
     * @param SolrQuery                $solrQuery                      solr query
     * @param bool                     $mongoDbCounterEnabled          mongoDbCounterEnabled
     * @param ?string                  $enableMongoDbCounterHeaderName enableMongoDbCounterHeaderName
     */
    public function __construct(
        LoggerInterface $logger,
        RequestParser $requestParser,
        VisitorInterface $visitor,
        int $paginationDefaultLimit,
        EventDispatcherInterface $eventDispatcher,
        SolrQuery $solrQuery,
        bool $mongoDbCounterEnabled,
        ?string $enableMongoDbCounterHeaderName
    ) {
        $this->logger = $logger;
        $this->rqlRequestParser = $requestParser;
        $this->visitor = $visitor;
        $this->paginationDefaultLimit = $paginationDefaultLimit;
        $this->eventDispatcher = $eventDispatcher;
        $this->solrQuery = $solrQuery;
        $this->mongoDbCounterEnabled = $mongoDbCounterEnabled;
        $this->enableMongoDbCounterHeaderName = $enableMongoDbCounterHeaderName;
    }

    /**
     * toggle flag if we should use mongodb secondary
     *
     * @param bool $isUseSecondary if secondary or not
     *
     * @return void
     */
    public function setIsUseSecondary(bool $isUseSecondary): void
    {
        $this->isUseSecondary = $isUseSecondary;
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

        // if id is *not* empty, then a single document is requested!
        $singleDocumentRequest = !empty($this->getDocumentId());

        $this->applyRqlQuery($singleDocumentRequest);

        if ($this->isUseSecondary) {
            $readPreference = new ReadPreference(ReadPreference::RP_SECONDARY_PREFERRED);
        } else {
            $readPreference = new ReadPreference(ReadPreference::RP_PRIMARY);
        }

        // dispatch our event if normal builder
        if ($this->queryBuilder instanceof Builder) {
            $this->queryBuilder = $this->executeQueryEvent($this->queryBuilder);
            $this->queryBuilder = $this->queryBuilder->setReadPreference($readPreference);
        }

        if ($this->queryBuilder instanceof \Doctrine\ODM\MongoDB\Aggregation\Builder) {
            /**
             * this is only the case when queryBuilder was overridden, most likely via a PostEvent
             * in the rql parsing phase.
             */
            $this->queryBuilder->hydrate($repository->getClassName());

            $this->logger->info(
                'QueryService: Aggregate query',
                [
                    'readPref' => $readPreference->getModeString()
                ]
            );

            $records = array_values(
                $this->queryBuilder->getAggregation(['readPreference' => $readPreference])->getIterator()->toArray()
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
                $countQueryBuilder = clone $this->queryBuilder;
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

            $mainQueryParts = $this->queryBuilder->getQuery()->getQuery();
            if (!isset($mainQueryParts['limit'])) {
                $mainQueryParts['limit'] = $this->getPaginationPageSize();
            }

            // save original size!
            $originalPagesize = $mainQueryParts['limit'];
            // set one more on querybuilder!
            $this->queryBuilder->limit($originalPagesize + 1);

            $mainQuery = $this->queryBuilder->getQuery();

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
            $this->queryBuilder->field('id')->equals($this->getDocumentId());

            $this->logger->info(
                'QueryService: getAction query',
                ['q' => $this->queryBuilder->getQuery()->getQuery(), 'readPref' => $readPreference->getModeString()]
            );

            $query = $this->queryBuilder->getQuery();
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
            $page = (int) ceil($this->getPaginationSkip() / $this->getPaginationPageSize()) + 1;
            $request->attributes->set('page', $page);

            $request->attributes->set('startAt', $this->getPaginationSkip());
            $request->attributes->set('perPage', $this->getPaginationPageSize());

            if ($request->attributes->has('totalCount')) {
                $numPages = (int) ceil($request->attributes->get('totalCount') / $this->getPaginationPageSize());
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
     * @return string|null either document id or null
     */
    private function getDocumentId()
    {
        return $this->request->attributes->get('singleDocument', null);
    }

    /**
     * apply all stuff from the rql query (if any) to the local querybuilder
     *
     * @param bool $singleDocumentRequest if single document is requested
     *
     * @return void
     */
    private function applyRqlQuery(bool $singleDocumentRequest)
    {
        $rqlQuery = $this->getRqlQuery($singleDocumentRequest);

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
            $currentQuery = $this->queryBuilder->getQuery()->getQuery();

            /*** default sort ***/

            if (!array_key_exists('sort', $currentQuery)) {
                $this->queryBuilder->sort('_id');
            }

            /*** pagination stuff ***/
            if (!array_key_exists('limit', $currentQuery)) {
                $this->queryBuilder->skip($this->getPaginationSkip());
                $this->queryBuilder->limit($this->getPaginationPageSize());
            }
        }
    }

    /**
     * returns the correct rql query for the request, including optional specified restrictions
     * in the service definition (via restrictionManager)
     *
     * @param bool $singleDocumentRequest if single document is requested
     *
     * @return Query|null the query
     */
    private function getRqlQuery(bool $singleDocumentRequest) : ?Query
    {
        $res = $this->rqlRequestParser->parse($this->request);

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
                    throw new RqlOperatorNotAllowedException($node->getNodeName());;
                }
            }
        }

        return $query;
    }

    /**
     * Check if collection has search indexes in DB
     *
     * @return bool
     */
    private function hasSearchIndex()
    {
        if ($this->solrQuery->hasSolr($this->repository->getClassName())) {
            return true;
        }

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
