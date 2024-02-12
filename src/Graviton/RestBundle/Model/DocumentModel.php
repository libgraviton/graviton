<?php
/**
 * Use doctrine odm as backend
 */

namespace Graviton\RestBundle\Model;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Graviton\RestBundle\Event\EntityPrePersistEvent;
use Graviton\RestBundle\Event\ModelEvent;
use Graviton\RestBundle\Service\QueryService;
use Graviton\RestBundle\Service\RestUtils;
use Graviton\SecurityBundle\Service\SecurityUtils;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Graviton\ExceptionBundle\Exception\NotFoundException;
use Graviton\ExceptionBundle\Exception\RecordOriginModifiedException;

/**
 * Use doctrine odm as backend
 *
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
class DocumentModel
{
    /**
     * @var string
     */
    protected $description;

    /**
     * @var QueryService
     */
    private $queryService;

    /**
     * @var string
     */
    private string $schemaPath;

    /**
     * @var array
     */
    protected $notModifiableOriginRecords;
    protected EventDispatcherInterface $eventDispatcher;
    private RestUtils $restUtils;
    private SecurityUtils $securityUtils;

    protected string $documentClassName;
    protected DocumentManager $documentManager;

    /**
     * constructor
     *
     * @param string          $schemaPath        schema path
     * @param string          $documentClassName class name
     * @param DocumentManager $documentManager   dm
     */
    public function __construct(string $schemaPath, string $documentClassName, DocumentManager $documentManager)
    {
        $this->schemaPath = $schemaPath;
        $this->documentClassName = $documentClassName;
        $this->documentManager = $documentManager;
    }

    /**
     * set query service
     *
     * @param QueryService $queryService qs
     *
     * @return void
     */
    public function setQueryService(QueryService $queryService)
    {
        $this->queryService = $queryService;
    }

    /**
     * set security utils
     *
     * @param SecurityUtils $securityUtils utils
     *
     * @return void
     */
    public function setSecurityUtils(SecurityUtils $securityUtils)
    {
        $this->securityUtils = $securityUtils;
    }

    /**
     * set event dispatcher
     *
     * @param EventDispatcherInterface $eventDispatcher ed
     *
     * @return void
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * set notModifiableOriginRecords
     *
     * @param array $notModifiableOriginRecords arr
     *
     * @return void
     */
    public function setNotModifiableOriginRecords($notModifiableOriginRecords)
    {
        $this->notModifiableOriginRecords = $notModifiableOriginRecords;
    }

    /**
     * set restutils
     *
     * @param RestUtils $restUtils ru
     *
     * @return void
     */
    public function setRestUtils(RestUtils $restUtils)
    {
        $this->restUtils = $restUtils;
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
        $this->queryService->setIsUseSecondary($isUseSecondary);
    }

    /**
     * get repository instance
     *
     * @return DocumentRepository
     */
    public function getRepository(): DocumentRepository
    {
        return $this->documentManager->getRepository($this->documentClassName);
    }

    /**
     * get schema path
     *
     * @return string schema path
     */
    public function getSchemaPath(): string
    {
        return $this->schemaPath;
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
        return $this->queryService->getWithRequest($request, $this->getRepository());
    }

    /**
     * inserts a record
     *
     * @param object $entity entity to insert
     *
     * @return Object|null entity or null
     */
    public function insertRecord($entity)
    {
        $entity = $this->dispatchPrePersistEvent($entity);

        // ensure meta fields!
        if (is_callable([$entity, 'set_CreatedBy'])) {
            $entity->set_CreatedBy($this->securityUtils->getSecurityUsername());
        }
        if (is_callable([$entity, 'set_CreatedAt'])) {
            $entity->set_CreatedAt(new \DateTime());
        }

        $this->documentManager->persist($entity);
        $this->documentManager->flush();

        // Fire ModelEvent
        $this->dispatchModelEvent(ModelEvent::MODEL_EVENT_INSERT, $entity);

        return $entity;
    }

    /**
     * finds a single entity
     *
     * @param string  $documentId id of entity to find
     * @param boolean $forceClear if we should clear the repository prior to fetching
     *
     * @return Object
     * @throws NotFoundException
     */
    public function find($documentId, $forceClear = false)
    {
        if ($forceClear) {
            $this->documentManager->clear($this->getEntityClass());
        }

        $builder = $this->getRepository()->createQueryBuilder()
            ->field('id')
            ->equals($documentId);

        $builder = $this->queryService->executeQueryEvent($builder);

        $result = $builder->getQuery()->getSingleResult();

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
     * @return string Serialised object
     * @throws NotFoundException
     */
    public function getSerialised($documentId, Request $request = null)
    {
        if (is_null($request)) {
            $request = Request::create('');
        }

        $request->attributes->set('singleDocument', $documentId);

        $document = $this->queryService->getWithRequest($request, $this->getRepository());
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
     * @param string $documentId id of entity to update
     * @param Object $entity     new entity
     *
     * @return Object|null
     */
    public function updateRecord($documentId, $entity)
    {
        $entity = $this->dispatchPrePersistEvent($entity);

        if (!is_null($documentId)) {
            $collection = $this->documentManager->getDocumentCollection($this->getEntityClass());
            $existing = $collection->findOne(
                ['_id' => $documentId],
                ['projection' => ['_createdAt' => 1, '_createdBy' => 1, '_id' => 1]]
            );

            $this->deleteById($documentId);

            // detach so odm knows it's gone
            $this->documentManager->detach($entity);
            $this->documentManager->clear($this->getEntityClass());

            // pass old attrs to new one.
            if (is_callable([$entity, 'set_CreatedBy']) && !empty($existing['_createdBy'])) {
                $entity->set_CreatedBy($existing['_createdBy']);
            }
            if (is_callable([$entity, 'set_CreatedAt']) && !empty($existing['_createdAt'])) {
                $entity->set_CreatedAt($existing['_createdAt']);
            }
        }

        if (is_callable([$entity, 'setLastModifiedBy'])) {
            $entity->setLastModifiedBy($this->securityUtils->getSecurityUsername());
        }
        if (is_callable([$entity, 'setLastModifiedAt'])) {
            $entity->setLastModifiedAt(new \DateTime());
        }

        $entity = $this->documentManager->merge($entity);

        $this->documentManager->persist($entity);
        $this->documentManager->flush();
        $this->documentManager->detach($entity);

        // Fire ModelEvent
        $this->dispatchModelEvent(ModelEvent::MODEL_EVENT_UPDATE, $entity);

        return $entity;
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

        // dispatch our event
        $this->dispatchPrePersistEvent($entity);

        $this->checkIfOriginRecord($entity);

        $return = $entity;

        if (is_callable([$entity, 'getId']) && $entity->getId() != null) {
            $this->deleteById($entity->getId());
            // detach so odm knows it's gone
            $this->documentManager->detach($entity);
            $this->documentManager->clear($this->getEntityClass());
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
        $this->documentManager->flush($document);
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
        $builder = $this->getRepository()->createQueryBuilder();
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
     * @return array|null|object record
     */
    public function selectSingleFields($id, array $fields, $hydrate = true)
    {
        $builder = $this->getRepository()->createQueryBuilder();
        $idField = $this->getRepository()->getClassMetadata()->getIdentifier()[0];

        $queryBuilder = $builder
            ->field($idField)->equals($id)
            ->select($fields);

        $queryBuilder = $this->queryService->executeQueryEvent($queryBuilder);

        $record = $queryBuilder
            ->hydrate($hydrate)
            ->getQuery()
            ->getSingleResult();

        return $record;
    }

    /**
     * get classname of entity
     *
     * @param bool $shortName if shortname or not
     *
     * @return string|null
     */
    public function getEntityClass(bool $shortName = false)
    {
        if ($shortName) {
            $parts = explode('\\', $this->documentClassName);
            return array_pop($parts);
        }

        return $this->documentClassName;
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
                throw new RecordOriginModifiedException(
                    sprintf(
                        "'recordOrigin' must not be one of the following keywords: %s",
                        implode(', ', $values)
                    )
                );
            }
        }
    }


    /**
     * Will fire a ModelEvent
     *
     * @param string $action     insert or update
     * @param object $collection the changed Document
     *
     * @return void
     */
    private function dispatchModelEvent($action, $collection)
    {
        if (!($this->getRepository() instanceof DocumentRepository)) {
            return;
        }
        if (!method_exists($collection, 'getId')) {
            return;
        }

        $event = new ModelEvent();
        $event->setCollectionId($collection->getId());
        $event->setActionByDispatchName($action);
        $event->setCollectionName($this->getRepository()->getClassMetadata()->getCollection());
        $event->setCollectionClass($this->getRepository()->getClassName());
        $event->setCollection($collection);

        $this->eventDispatcher->dispatch($event, $action);
    }

    /**
     * dispatches our pre-persist event
     *
     * @param object $entity entity
     *
     * @return object entity
     */
    private function dispatchPrePersistEvent(object $entity)
    {
        $event = new EntityPrePersistEvent();
        $event->setEntity($entity);
        $event->setRepository($this->getRepository());
        $event = $this->eventDispatcher->dispatch($event, EntityPrePersistEvent::NAME);
        return $event->getEntity();
    }
}
