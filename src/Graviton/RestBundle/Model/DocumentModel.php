<?php
/**
 * Use doctrine odm as backend
 */

namespace Graviton\RestBundle\Model;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Graviton\RestBundle\Event\ModelEvent;
use Graviton\RestBundle\Service\QueryService;
use Graviton\SchemaBundle\Model\SchemaModel;
use Graviton\RestBundle\Service\RestUtils;
use Symfony\Component\HttpFoundation\Request;
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
    protected $requiredFields = [];
    /**
     * @var string[]
     */
    protected $searchableFields = [];
    /**
     * @var string[]
     */
    protected $textIndexes = [];
    /**
     * @var DocumentRepository
     */
    private $repository;
    /**
     * @var QueryService
     */
    private $queryService;
    /**
     * @var array
     */
    protected $notModifiableOriginRecords;
    /**
     * @var DocumentManager
     */
    protected $manager;
    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;
    /**
     * @var RestUtils
     */
    private $restUtils;

    /**
     * @param QueryService    $queryService               query service
     * @param RestUtils       $restUtils                  Rest utils
     * @param EventDispatcher $eventDispatcher            Kernel event dispatcher
     * @param array           $notModifiableOriginRecords strings with not modifiable recordOrigin values
     */
    public function __construct(
        QueryService $queryService,
        RestUtils $restUtils,
        $eventDispatcher,
        $notModifiableOriginRecords
    ) {
        parent::__construct();
        $this->queryService = $queryService;
        $this->eventDispatcher = $eventDispatcher;
        $this->notModifiableOriginRecords = $notModifiableOriginRecords;
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
        return $this->queryService->getWithRequest($request, $this->repository);
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
     *
     * @throws NotFoundException
     * @return string Serialised object
     */
    public function getSerialised($documentId, Request $request = null)
    {
        if (is_null($request)) {
            $request = Request::create('');
        }

        $request->attributes->set('singleDocument', $documentId);

        $document = $this->queryService->getWithRequest($request, $this->repository);
        if (empty($document)) {
            throw new NotFoundException(
                sprintf(
                    "Entry with id '%s' not found!",
                    $documentId
                )
            );
        }

        return $this->restUtils->serialize($document);
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
