<?php
/**
 * Use doctrine odm as backend
 */

namespace Graviton\RestBundle\Model;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Graviton\RestBundle\Service\RqlTranslator;
use Graviton\Rql\Node\SearchNode;
use Graviton\SchemaBundle\Model\SchemaModel;
use Graviton\SecurityBundle\Entities\SecurityUser;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ODM\MongoDB\Query\Builder;
use Graviton\Rql\Visitor\MongoOdm as Visitor;
use Xiag\Rql\Parser\AbstractNode;
use Xiag\Rql\Parser\Node\Query\AbstractLogicOperatorNode;
use Xiag\Rql\Parser\Query;
use Graviton\ExceptionBundle\Exception\RecordOriginModifiedException;
use Xiag\Rql\Parser\Exception\SyntaxErrorException as RqlSyntaxErrorException;
use Graviton\SchemaBundle\Document\Schema as SchemaDocument;
use Xiag\Rql\Parser\Query as XiagQuery;

/**
 * Use doctrine odm as backend
 *
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
     * @var RqlTranslator
     */
    protected $translator;

    /**
     * @var DocumentManager
     */
    protected $manager;

    /**
     * @param Visitor       $visitor                    rql query visitor
     * @param RqlTranslator $translator                 Translator for query modification
     * @param array         $notModifiableOriginRecords strings with not modifiable recordOrigin values
     * @param integer       $paginationDefaultLimit     amount of data records to be returned when in pagination context
     */
    public function __construct(
        Visitor $visitor,
        RqlTranslator $translator,
        $notModifiableOriginRecords,
        $paginationDefaultLimit
    ) {
        parent::__construct();
        $this->visitor = $visitor;
        $this->translator = $translator;
        $this->notModifiableOriginRecords = $notModifiableOriginRecords;
        $this->paginationDefaultLimit = (int) $paginationDefaultLimit;
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
     * @param Request        $request The request object
     * @param SecurityUser   $user    SecurityUser Object
     * @param SchemaDocument $schema  Schema model used for search fields extraction
     *
     * @return array
     */
    public function findAll(Request $request, SecurityUser $user = null, SchemaDocument $schema = null)
    {
        $pageNumber = $request->query->get('page', 1);
        $numberPerPage = (int) $request->query->get('perPage', $this->getDefaultLimit());
        $startAt = ($pageNumber - 1) * $numberPerPage;
        // Only 1 search text node allowed.
        $hasSearch = false;

        /** @var \Doctrine\ODM\MongoDB\Query\Builder $queryBuilder */
        $queryBuilder = $this->repository
            ->createQueryBuilder();

        if ($this->filterByAuthUser && $user && $user->hasRole(SecurityUser::ROLE_USER)) {
            $queryBuilder->field($this->filterByAuthField)->equals($user->getUser()->getId());
        }

        // *** do we have an RQL expression, do we need to filter data?
        if ($request->attributes->get('hasRql', false)) {
            $innerQuery = $request->attributes->get('rqlQuery')->getQuery();
            $xiagQuery = new XiagQuery();
            // can we perform a search in an index instead of filtering?
            if ($innerQuery instanceof AbstractLogicOperatorNode) {
                foreach ($innerQuery->getQueries() as $innerRql) {
                    if (!$hasSearch && $innerRql instanceof SearchNode) {
                        $searchString = implode('&', $innerRql->getSearchTerms());
                        $queryBuilder->addAnd(
                            $queryBuilder->expr()->text($searchString)
                        );
                        $hasSearch = true;
                    } else {
                        $xiagQuery->setQuery($innerRql);
                    }
                }
            } elseif ($this->hasCustomSearchIndex() && ($innerQuery instanceof SearchNode)) {
                $searchString = implode('&', $innerQuery->getSearchTerms());
                $queryBuilder->addAnd(
                    $queryBuilder->expr()->text($searchString)
                );
                $hasSearch = true;
            } elseif ($innerQuery instanceof AbstractLogicOperatorNode) {
                /** @var AbstractLogicOperatorNode $innerQuery */
                foreach ($innerQuery->getQueries() as $innerRql) {
                    if (!$innerRql instanceof SearchNode) {
                        $xiagQuery->setQuery($innerRql);
                    }
                }
            } elseif ($innerQuery instanceof AbstractNode) {
                $xiagQuery->setQuery($innerQuery);
            }

            $queryBuilder = $this->doRqlQuery(
                $queryBuilder,
                $xiagQuery
            );
        } else {
            // @todo [lapistano]: seems the offset is missing for this query.
            /** @var \Doctrine\ODM\MongoDB\Query\Builder $qb */
            $queryBuilder->find($this->repository->getDocumentName());
        }

        // define offset and limit
        if (!array_key_exists('skip', $queryBuilder->getQuery()->getQuery())) {
            $queryBuilder->skip($startAt);
        } else {
            $startAt = (int) $queryBuilder->getQuery()->getQuery()['skip'];
        }

        if (!array_key_exists('limit', $queryBuilder->getQuery()->getQuery())) {
            $queryBuilder->limit($numberPerPage);
        } else {
            $numberPerPage = (int) $queryBuilder->getQuery()->getQuery()['limit'];
        }

        // Limit can not be negative nor null.
        if ($numberPerPage < 1) {
            throw new RqlSyntaxErrorException('negative or null limit in rql');
        }

        /**
         * add a default sort on id if none was specified earlier
         *
         * not specifying something to sort on leads to very weird cases when fetching references
         * If search node, sort by Score
         * TODO Review this sorting, not 100% sure
         */
        if ($hasSearch && !array_key_exists('sort', $queryBuilder->getQuery()->getQuery())) {
            $queryBuilder->sortMeta('score', 'textScore');
        } elseif (!array_key_exists('sort', $queryBuilder->getQuery()->getQuery())) {
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
     * @param string $prefix the prefix for custom text search indexes
     * @return bool
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    private function hasCustomSearchIndex($prefix = 'search')
    {
        $collection = $this->repository->getDocumentManager()->getDocumentCollection($this->repository->getClassName());
        $indexesInfo = $collection->getIndexInfo();
        foreach ($indexesInfo as $indexInfo) {
            if ($indexInfo['name']==$prefix.$collection->getName().'Index') {
                return true;
            }
        }
        return false;
    }

    /**
     * @return string the version of the MongoDB as a string
     */
    private function getMongoDBVersion()
    {
        $buildInfo = $this->repository->getDocumentManager()->getDocumentDatabase(
            $this->repository->getClassName()
        )->command(['buildinfo'=>1]);
        if (isset($buildInfo['version'])) {
            return $buildInfo['version'];
        } else {
            return "unkown";
        }
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
        $this->checkIfOriginRecord($entity);
        $this->manager->persist($entity);

        if ($doFlush) {
            $this->manager->flush($entity);
        }
        if ($returnEntity) {
            return $this->find($entity->getId());
        }
    }

    /**
     * @param string $documentId id of entity to find
     *
     * @return Object
     */
    public function find($documentId)
    {
        return $this->repository->find($documentId);
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
        // In both cases the document attribute named originRecord must not be 'core'
        $this->checkIfOriginRecord($entity);
        $this->checkIfOriginRecord($this->selectSingleFields($documentId, ['recordOrigin']));

        if (!is_null($documentId)) {
            $this->deleteById($documentId);
            // detach so odm knows it's gone
            $this->manager->detach($entity);
            $this->manager->clear();
        }

        $entity = $this->manager->merge($entity);

        $this->manager->persist($entity);
        $this->manager->flush($entity);

        if ($returnEntity) {
            return $entity;
        }
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

        $return = $entity;
        if ($entity) {
            $this->checkIfOriginRecord($entity);
            $this->manager->remove($entity);
            $this->manager->flush();
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
     * @return array
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
     * @param Boolean $active active
     * @param String  $field  field
     * @return void
     */
    public function setFilterByAuthUser($active, $field)
    {
        $this->filterByAuthUser = is_bool($active) ? $active : false;
        $this->filterByAuthField = $field;
    }
}
