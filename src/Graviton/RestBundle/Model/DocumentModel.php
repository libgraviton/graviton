<?php
/**
 * Use doctrine odm as backend
 */

namespace Graviton\RestBundle\Model;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Expr;
use Graviton\DocumentBundle\Service\CollectionCache;
use Graviton\RestBundle\Event\ModelEvent;
use Graviton\Rql\Node\SearchNode;
use Graviton\Rql\Visitor\MongoOdm as Visitor;
use Graviton\SchemaBundle\Model\SchemaModel;
use Graviton\RestBundle\Service\RestUtils;
use MongoDB\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Xiag\Rql\Parser\Node\LimitNode;
use Xiag\Rql\Parser\Node\Query\LogicOperator\AndNode;
use Xiag\Rql\Parser\Query;
use Xiag\Rql\Parser\Exception\SyntaxErrorException as RqlSyntaxErrorException;
use Xiag\Rql\Parser\Query as XiagQuery;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher as EventDispatcher;
use Graviton\ExceptionBundle\Exception\NotFoundException;
use Graviton\ExceptionBundle\Exception\RecordOriginModifiedException;

/**
 * Use doctrine odm as backend
 *
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
class DocumentModel extends SchemaModel implements ModelInterface
{
    /**
     * @var string
     */
    protected $description;
    /**
     * @var string[]
     */
    protected $fieldTitles;
    /**
     * @var string[]
     */
    protected $fieldDescriptions;
    /**
     * @var string[]
     */
    protected $requiredFields = array();
    /**
     * @var string[]
     */
    protected $searchableFields = array();
    /**
     * @var string[]
     */
    protected $textIndexes = array();
    /**
     * @var DocumentRepository
     */
    private $repository;
    /**
     * @var Visitor
     */
    private $visitor;
    /**
     * @var array
     */
    protected $notModifiableOriginRecords;
    /**
     * @var  integer
     */
    private $paginationDefaultLimit;

    /**
     * @var boolean
     */
    protected $filterByAuthUser;

    /**
     * @var string
     */
    protected $filterByAuthField;

    /**
     * @var DocumentManager
     */
    protected $manager;

    /** @var EventDispatcher */
    protected $eventDispatcher;

    /** @var $collectionCache */
    protected $cache;

    /**
     * @var RestUtils
     */
    private $restUtils;

    /**
     * @param Visitor         $visitor                    rql query visitor
     * @param RestUtils       $restUtils                  Rest utils
     * @param EventDispatcher $eventDispatcher            Kernel event dispatcher
     * @param CollectionCache $collectionCache            Cache Service
     * @param array           $notModifiableOriginRecords strings with not modifiable recordOrigin values
     * @param integer         $paginationDefaultLimit     amount of data records to be returned when in pagination cnt
     */
    public function __construct(
        Visitor $visitor,
        RestUtils $restUtils,
        $eventDispatcher,
        CollectionCache $collectionCache,
        $notModifiableOriginRecords,
        $paginationDefaultLimit
    ) {
        parent::__construct();
        $this->visitor = $visitor;
        $this->eventDispatcher = $eventDispatcher;
        $this->notModifiableOriginRecords = $notModifiableOriginRecords;
        $this->paginationDefaultLimit = (int) $paginationDefaultLimit;
        $this->cache = $collectionCache;
        $this->restUtils = $restUtils;
    }

    /**
     * get repository instance
     *
     * @return DocumentRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * create new app model
     *
     * @param DocumentRepository $repository Repository of countries
     *
     * @return \Graviton\RestBundle\Model\DocumentModel
     */
    public function setRepository(DocumentRepository $repository)
    {
        $this->repository = $repository;
        $this->manager = $repository->getDocumentManager();

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @param Request $request The request object
     *
     * @return array
     */
    public function findAll(Request $request)
    {
        $pageNumber = $request->query->get('page', 1);
        $numberPerPage = (int) $request->query->get('perPage', $this->getDefaultLimit());
        $startAt = ($pageNumber - 1) * $numberPerPage;

        /** @var XiagQuery $xiagQuery */
        $xiagQuery = $request->attributes->get('rqlQuery');

        /** @var Builder $queryBuilder */
        $queryBuilder = $this->repository
            ->createQueryBuilder();

        // Setting RQL Query
        if ($xiagQuery) {
            // Check if search and if this Repository have search indexes.
            if ($query = $xiagQuery->getQuery()) {
                if ($query instanceof AndNode) {
                    foreach ($query->getQueries() as $xq) {
                        if ($xq instanceof SearchNode && !$this->hasCustomSearchIndex()) {
                            throw new InvalidArgumentException('Current api request have search index');
                        }
                    }
                } elseif ($query instanceof SearchNode && !$this->hasCustomSearchIndex()) {
                    throw new InvalidArgumentException('Current api request have search index');
                }
            }
            // Clean up Search rql param and set it as Doctrine query
            $queryBuilder = $this->doRqlQuery(
                $queryBuilder,
                $xiagQuery
            );
        } else {
            // @todo [lapistano]: seems the offset is missing for this query.
            /** @var \Doctrine\ODM\MongoDB\Query\Builder $qb */
            $queryBuilder->find($this->repository->getDocumentName());
        }

        /** @var LimitNode $rqlLimit */
        $rqlLimit = $xiagQuery instanceof XiagQuery ? $xiagQuery->getLimit() : false;

        // define offset and limit
        if (!$rqlLimit || !$rqlLimit->getOffset()) {
            $queryBuilder->skip($startAt);
        } else {
            $startAt = (int) $rqlLimit->getOffset();
            $queryBuilder->skip($startAt);
        }

        if (!$rqlLimit || is_null($rqlLimit->getLimit())) {
            $queryBuilder->limit($numberPerPage);
        } else {
            $numberPerPage = (int) $rqlLimit->getLimit();
            $queryBuilder->limit($numberPerPage);
        }

        // Limit can not be negative nor null.
        if ($numberPerPage < 1) {
            throw new RqlSyntaxErrorException('negative or null limit in rql');
        }

        /**
         * add a default sort on id if none was specified earlier
         *
         * not specifying something to sort on leads to very weird cases when fetching references.
         */
        if (!array_key_exists('sort', $queryBuilder->getQuery()->getQuery())) {
            $queryBuilder->sort('_id');
        }

        // run query
        $query = $queryBuilder->getQuery();
        $records = array_values($query->execute()->toArray());

        $totalCount = $query->count();
        $numPages = (int) ceil($totalCount / $numberPerPage);
        $page = (int) ceil($startAt / $numberPerPage) + 1;
        if ($numPages > 1) {
            $request->attributes->set('paging', true);
            $request->attributes->set('page', $page);
            $request->attributes->set('numPages', $numPages);
            $request->attributes->set('startAt', $startAt);
            $request->attributes->set('perPage', $numberPerPage);
            $request->attributes->set('totalCount', $totalCount);
        }

        return $records;
    }

    /**
     * Check if collection has search indexes in DB
     *
     * @param string $prefix the prefix for custom text search indexes
     * @return bool
     */
    private function hasCustomSearchIndex($prefix = 'search_')
    {
        $metadata = $this->repository->getClassMetadata();
        $indexes = $metadata->getIndexes();
        if (count($indexes) < 1) {
            return false;
        }
        $collectionsName = substr($metadata->getName(), strrpos($metadata->getName(), '\\') + 1);
        $searchIndexName = $prefix.$collectionsName.'_index';
        // We reverse as normally the search index is the last.
        foreach (array_reverse($indexes) as $index) {
            if (array_key_exists('options', $index) &&
                array_key_exists('name', $index['options']) &&
                $searchIndexName == $index['options']['name']
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param object $entity       entity to insert
     * @param bool   $returnEntity true to return entity
     * @param bool   $doFlush      if we should flush or not after insert
     *
     * @return Object|null
     */
    public function insertRecord($entity, $returnEntity = true, $doFlush = true)
    {
        $this->manager->persist($entity);

        if ($doFlush) {
            $this->manager->flush($entity);
        }

        // Fire ModelEvent
        $this->dispatchModelEvent(ModelEvent::MODEL_EVENT_INSERT, $entity);

        if ($returnEntity) {
            return $this->find($entity->getId());
        }
        return null;
    }

    /**
     * @param string $documentId id of entity to find
     *
     * @throws NotFoundException
     * @return Object
     */
    public function find($documentId)
    {
        $result = $this->repository->find($documentId);

        if (empty($result)) {
            throw new NotFoundException("Entry with id " . $documentId . " not found!");
        }

        return $result;
    }

    /**
     * Will attempt to find Document by ID.
     * If config cache is enabled for document it will save it.
     *
     * @param string  $documentId id of entity to find
     * @param Request $request    request
     * @param bool    $skipLock   if true, we don't check for the lock
     *
     * @throws NotFoundException
     * @return string Serialised object
     */
    public function getSerialised($documentId, Request $request = null, $skipLock = false)
    {
        if (($request instanceof Request)  &&
            ($query = $request->attributes->get('rqlQuery')) &&
            (($query instanceof XiagQuery))
        ) {
            /** @var Builder $queryBuilder */
            $queryBuilder = $this->doRqlQuery($this->repository->createQueryBuilder(), $query);
            $queryBuilder->field('id')->equals($documentId);
            $result = $queryBuilder->getQuery()->getSingleResult();
            if (empty($result)) {
                throw new NotFoundException("Entry with id " . $documentId . " not found!");
            }
            $document = $this->restUtils->serialize($result);
        } elseif ($cached = $this->cache->getByRepository($this->repository, $documentId)) {
            $document = $cached;
        } else {
            if (!$skipLock) {
                $this->cache->updateOperationCheck($this->repository, $documentId);
            }
            $document = $this->restUtils->serialize($this->find($documentId));
            $this->cache->setByRepository($this->repository, $document, $documentId);
        }

        return $document;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $documentId   id of entity to update
     * @param Object $entity       new entity
     * @param bool   $returnEntity true to return entity
     *
     * @return Object|null
     */
    public function updateRecord($documentId, $entity, $returnEntity = true)
    {
        if (!is_null($documentId)) {
            $this->deleteById($documentId);
            // detach so odm knows it's gone
            $this->manager->detach($entity);
            $this->manager->clear();
        }

        $entity = $this->manager->merge($entity);

        $this->manager->persist($entity);
        $this->manager->flush($entity);

        // Fire ModelEvent
        $this->dispatchModelEvent(ModelEvent::MODEL_EVENT_UPDATE, $entity);

        if ($returnEntity) {
            return $entity;
        }
        return null;
    }

    /**
     * {@inheritDoc}
     *
     * @param string|object $id id of entity to delete or entity instance
     *
     * @return null|Object
     */
    public function deleteRecord($id)
    {
        // Check and wait if another update is being processed, avoid double delete
        $this->cache->updateOperationCheck($this->repository, $id);
        $this->cache->addUpdateLock($this->repository, $id, 1);

        if (is_object($id)) {
            $entity = $id;
        } else {
            $entity = $this->find($id);
        }

        $this->checkIfOriginRecord($entity);
        $return = $entity;

        if (is_callable([$entity, 'getId']) && $entity->getId() != null) {
            $this->deleteById($entity->getId());
            // detach so odm knows it's gone
            $this->manager->detach($entity);
            $this->manager->clear();
            // Dispatch ModelEvent
            $this->dispatchModelEvent(ModelEvent::MODEL_EVENT_DELETE, $return);
            $return = null;
        }

        $this->cache->releaseUpdateLock($this->repository, $id);

        return $return;
    }

    /**
     * Triggers a flush on the DocumentManager
     *
     * @param null $document optional document
     *
     * @return void
     */
    public function flush($document = null)
    {
        $this->manager->flush($document);
    }

    /**
     * A low level delete without any checks
     *
     * @param mixed $id record id
     *
     * @return void
     */
    private function deleteById($id)
    {
        $builder = $this->repository->createQueryBuilder();
        $builder
            ->remove()
            ->field('id')->equals($id)
            ->getQuery()
            ->execute();
    }

    /**
     * Checks in a performant way if a certain record id exists in the database
     *
     * @param mixed $id record id
     *
     * @return bool true if it exists, false otherwise
     */
    public function recordExists($id)
    {
        return is_array($this->selectSingleFields($id, ['id'], false));
    }

    /**
     * Returns a set of fields from an existing resource in a performant manner.
     * If you need to check certain fields on an object (and don't need everything), this
     * is a better way to get what you need.
     * If the record is not present, you will receive null. If you don't need an hydrated
     * instance, make sure to pass false there.
     *
     * @param mixed $id      record id
     * @param array $fields  list of fields you need.
     * @param bool  $hydrate whether to hydrate object or not
     *
     * @return array|null|object
     */
    public function selectSingleFields($id, array $fields, $hydrate = true)
    {
        $builder = $this->repository->createQueryBuilder();
        $idField = $this->repository->getClassMetadata()->getIdentifier()[0];

        $record = $builder
            ->field($idField)->equals($id)
            ->select($fields)
            ->hydrate($hydrate)
            ->getQuery()
            ->getSingleResult();

        return $record;
    }

    /**
     * get classname of entity
     *
     * @return string|null
     */
    public function getEntityClass()
    {
        if ($this->repository instanceof DocumentRepository) {
            return $this->repository->getDocumentName();
        }

        return null;
    }

    /**
     * {@inheritDoc}
     *
     * Currently this is being used to build the route id used for redirecting
     * to newly made documents. It might benefit from having a different name
     * for those purposes.
     *
     * We might use a convention based mapping here:
     * Graviton\CoreBundle\Document\App -> mongodb://graviton_core
     * Graviton\CoreBundle\Entity\Table -> mysql://graviton_core
     *
     * @todo implement this in a more convention based manner
     *
     * @return string
     */
    public function getConnectionName()
    {
        $bundle = strtolower(substr(explode('\\', get_class($this))[1], 0, -6));

        return 'graviton.' . $bundle;
    }

    /**
     * Does the actual query using the RQL Bundle.
     *
     * @param Builder $queryBuilder Doctrine ODM QueryBuilder
     * @param Query   $query        query from parser
     *
     * @return Builder|Expr
     */
    protected function doRqlQuery($queryBuilder, Query $query)
    {
        $this->visitor->setBuilder($queryBuilder);

        return $this->visitor->visit($query);
    }

    /**
     * Checks the recordOrigin attribute of a record and will throw an exception if value is not allowed
     *
     * @param Object $record record
     *
     * @return void
     */
    protected function checkIfOriginRecord($record)
    {
        if ($record instanceof RecordOriginInterface
            && !$record->isRecordOriginModifiable()
        ) {
            $values = $this->notModifiableOriginRecords;
            $originValue = strtolower(trim($record->getRecordOrigin()));

            if (in_array($originValue, $values)) {
                $msg = sprintf("Must not be one of the following keywords: %s", implode(', ', $values));

                throw new RecordOriginModifiedException($msg);
            }
        }
    }

    /**
     * Determines the configured amount fo data records to be returned in pagination context.
     *
     * @return int
     */
    private function getDefaultLimit()
    {
        if (0 < $this->paginationDefaultLimit) {
            return $this->paginationDefaultLimit;
        }

        return 10;
    }

    /**
     * Will fire a ModelEvent
     *
     * @param string $action     insert or update
     * @param Object $collection the changed Document
     *
     * @return void
     */
    private function dispatchModelEvent($action, $collection)
    {
        if (!($this->repository instanceof DocumentRepository)) {
            return;
        }
        if (!method_exists($collection, 'getId')) {
            return;
        }

        $event = new ModelEvent();
        $event->setCollectionId($collection->getId());
        $event->setActionByDispatchName($action);
        $event->setCollectionName($this->repository->getClassMetadata()->getCollection());
        $event->setCollectionClass($this->repository->getClassName());
        $event->setCollection($collection);

        $this->eventDispatcher->dispatch($action, $event);
    }
}
