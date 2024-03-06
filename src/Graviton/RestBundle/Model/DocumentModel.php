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

/**
 * Use doctrine odm as backend
 *
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
readonly class DocumentModel
{

    /**
     * constructor
     *
     * @param string          $schemaPath        schema path
     * @param string          $runtimeDefFile    path to runtime def file
     * @param string          $documentClassName class name
     * @param DocumentManager $documentManager   dm
     */
    public function __construct(
        // common stuff
        private QueryService $queryService,
        protected EventDispatcherInterface $eventDispatcher,
        private RestUtils $restUtils,
        private SecurityUtils $securityUtils,
        protected DocumentManager $documentManager,
        // model specific stuff
        private string $schemaPath,
        private string $runtimeDefFile,
        private string $documentClassName
    ) {
    }

    /**
     * returns the runtime definition
     *
     * @return RuntimeDefinition runtime def
     */
    public function getRuntimeDefinition() : RuntimeDefinition
    {
        return unserialize(file_get_contents($this->runtimeDefFile));
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
        return $this->queryService->getWithRequest($request, $this);
    }

    /**
     * upserts an entity
     *
     * @param $record
     * @return void
     */
    public function upsertRecord(string $id, object $record, ?Request $request = null)
    {
        if (!$this->recordExists($id)) {
            $this->insertRecord($record, $request);
        } else {
            $this->updateRecord($id, $record, $request);
        }
    }

    /**
     * inserts a record
     *
     * @param object $entity entity to insert
     *
     * @return Object|null entity or null
     */
    public function insertRecord(object $entity, ?Request $request = null)
    {
        $entity = $this->dispatchPrePersistEvent($entity);

        $this->setChangeTrackingData($entity);

        $this->documentManager->persist($entity);
        $this->documentManager->flush();

        if (is_callable([$entity, 'getId'])) {
            $recordId = $entity->getId();

            if (!is_null($request)) {
                $this->addRequestAttributes($recordId, $request);
            }

            // Fire ModelEvent
            $this->dispatchModelEvent(ModelEvent::MODEL_EVENT_INSERT, $recordId, $request);
        }

        return $entity;
    }

    private function addRequestAttributes(string $id, Request $request)
    {
        $request->attributes->set('id', $id);
        $request->attributes->set('varnishTags', $this->getEntityClass(true));
    }

    /**
     * add change tracking to entity
     *
     * @param \stdClass $entity entity
     *
     * @return void
     */
    private function setChangeTrackingData($entity, $existing = null)
    {
        if (!is_null($existing)) {
            // pass old attrs to new one.
            if (is_callable([$entity, 'set_CreatedBy']) && !empty($existing['_createdBy'])) {
                $entity->set_CreatedBy($existing['_createdBy']);
            }
            if (is_callable([$entity, 'set_CreatedAt']) && !empty($existing['_createdAt'])) {
                $entity->set_CreatedAt($existing['_createdAt']);
            }
        }

        // ensure created stuff
        if (is_callable([$entity, 'get_CreatedBy']) && empty($entity->get_CreatedBy())) {
            if (is_callable([$entity, 'set_CreatedBy'])) {
                $entity->set_CreatedBy($this->securityUtils->getSecurityUsername());
            }
            if (is_callable([$entity, 'set_CreatedAt'])) {
                $entity->set_CreatedAt(new \DateTime());
            }
        }

        // always set modified
        if (is_callable([$entity, 'setLastModifiedBy'])) {
            $entity->setLastModifiedBy($this->securityUtils->getSecurityUsername());
        }
        if (is_callable([$entity, 'setLastModifiedAt'])) {
            $entity->setLastModifiedAt(new \DateTime());
        }
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
            $this->documentManager->clear();
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

        $document = $this->queryService->getWithRequest($request, $this);
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
    public function updateRecord(string $documentId, object $entity, ?Request $request = null)
    {
        $entity = $this->dispatchPrePersistEvent($entity);

        // see if we find existing
        $collection = $this->documentManager->getDocumentCollection($this->getEntityClass());
        $existing = $collection->findOne(
            ['_id' => $documentId],
            ['projection' => ['_createdAt' => 1, '_createdBy' => 1, '_id' => 1]]
        );

        $this->deleteById($documentId);

        // detach so odm knows it's gone
        $this->documentManager->detach($entity);
        $this->documentManager->clear();

        $this->setChangeTrackingData($entity, $existing);

        $entity = $this->documentManager->merge($entity);

        $this->documentManager->persist($entity);
        $this->documentManager->flush();
        $this->documentManager->detach($entity);

        if (!is_null($request)) {
            $this->addRequestAttributes($documentId, $request);
        }

        // Fire ModelEvent
        $this->dispatchModelEvent(ModelEvent::MODEL_EVENT_UPDATE, $documentId, $request);

        return $entity;
    }

    /**
     * {@inheritDoc}
     *
     * @param string|object $id id of entity to delete or entity instance
     *
     * @return null|Object
     */
    public function deleteRecord($id, ?Request $request = null)
    {
        if (is_object($id)) {
            $entity = $id;
        } else {
            $entity = $this->find($id);
        }

        // dispatch our event
        $this->dispatchPrePersistEvent($entity);

        $return = $entity;

        if (is_callable([$entity, 'getId']) && $entity->getId() != null) {
            $this->deleteById($entity->getId());
            // detach so odm knows it's gone
            $this->documentManager->detach($entity);
            $this->documentManager->clear();

            if (!is_null($request)) {
                $this->addRequestAttributes($entity->getId(), $request);
            }

            // Dispatch ModelEvent
            $this->dispatchModelEvent(ModelEvent::MODEL_EVENT_DELETE, (string) $id, $request);
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
     * Will fire a ModelEvent
     *
     * @param string $action     insert or update
     * @param object $collection the changed Document
     *
     * @return void
     */
    public function dispatchModelEvent(string $eventName, string $recordId, ?Request $request = null)
    {
        $event = new ModelEvent(
            $eventName,
            $recordId,
            $this,
            $request
        );

        $this->eventDispatcher->dispatch($event, $eventName);
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
