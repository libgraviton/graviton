<?php
/**
 * Use doctrine odm as backend
 */

namespace Graviton\RestBundle\Model;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Graviton\Rql\Node\SearchNode;
use Graviton\SchemaBundle\Model\SchemaModel;
use Graviton\SecurityBundle\Entities\SecurityUser;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ODM\MongoDB\Query\Builder;
use Graviton\Rql\Visitor\MongoOdm as Visitor;
use Xiag\Rql\Parser\Node\LimitNode;
use Xiag\Rql\Parser\Node\Query\AbstractLogicOperatorNode;
use Xiag\Rql\Parser\Query;
use Graviton\ExceptionBundle\Exception\RecordOriginModifiedException;
use Xiag\Rql\Parser\Exception\SyntaxErrorException as RqlSyntaxErrorException;
use Graviton\SchemaBundle\Document\Schema as SchemaDocument;
use Xiag\Rql\Parser\Query as XiagQuery;
use \Doctrine\ODM\MongoDB\Query\Builder as MongoBuilder;

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

    /**
     * @param Visitor $visitor                    rql query visitor
     * @param array   $notModifiableOriginRecords strings with not modifiable recordOrigin values
     * @param integer $paginationDefaultLimit     amount of data records to be returned when in pagination context
     */
    public function __construct(
        Visitor $visitor,
        $notModifiableOriginRecords,
        $paginationDefaultLimit
    ) {
        parent::__construct();
        $this->visitor = $visitor;
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
     * @param Request      $request The request object
     * @param SecurityUser $user    SecurityUser Object
     *
     * @return array
     */
    public function findAll(Request $request, SecurityUser $user = null)
    {
        $pageNumber = $request->query->get('page', 1);
        $numberPerPage = (int) $request->query->get('perPage', $this->getDefaultLimit());
        $startAt = ($pageNumber - 1) * $numberPerPage;

        /** @var XiagQuery $xiagQuery */
        $xiagQuery = $request->attributes->get('rqlQuery');

        /** @var MongoBuilder $queryBuilder */
        $queryBuilder = $this->repository
            ->createQueryBuilder();

        // Setting RQL Query
        if ($xiagQuery) {
            // Clean up Search rql param and set it as Doctrine query
            if ($xiagQuery->getQuery() && $this->hasCustomSearchIndex() && (float) $this->getMongoDBVersion() >= 2.6) {
                $searchQueries = $this->buildSearchQuery($xiagQuery, $queryBuilder);
                $xiagQuery = $searchQueries['xiagQuery'];
                $queryBuilder = $searchQueries['queryBuilder'];
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
     * @param XiagQuery    $xiagQuery    Xiag Builder
     * @param MongoBuilder $queryBuilder Mongo Doctrine query builder
     * @return array
     */
    private function buildSearchQuery(XiagQuery $xiagQuery, MongoBuilder $queryBuilder)
    {
        $innerQuery = $xiagQuery->getQuery();
        $hasSearch = false;
        $nodes = [];
        if ($innerQuery instanceof AbstractLogicOperatorNode) {
            foreach ($innerQuery->getQueries() as $key => $innerRql) {
                if ($innerRql instanceof SearchNode) {
                    if (!$hasSearch) {
                        $queryBuilder = $this->buildSearchTextQuery($queryBuilder, $innerRql);
                        $hasSearch = true;
                    }
                } else {
                    $nodes[] = $innerRql;
                }
            }
        } elseif ($innerQuery instanceof SearchNode) {
            $queryBuilder = $this->repository->createQueryBuilder();
            $queryBuilder = $this->buildSearchTextQuery($queryBuilder, $innerQuery);
            $hasSearch = true;
        }
        // Remove the Search from RQL xiag
        if ($hasSearch && $nodes) {
            $newXiagQuery = new XiagQuery();
            if ($xiagQuery->getLimit()) {
                $newXiagQuery->setLimit($xiagQuery->getLimit());
            }
            if ($xiagQuery->getSelect()) {
                $newXiagQuery->setSelect($xiagQuery->getSelect());
            }
            if ($xiagQuery->getSort()) {
                $newXiagQuery->setSort($xiagQuery->getSort());
            }
            $binderClass = get_class($innerQuery);
            /** @var AbstractLogicOperatorNode $newBinder */
            $newBinder = new $binderClass();
            foreach ($nodes as $node) {
                $newBinder->addQuery($node);
            }
            $newXiagQuery->setQuery($newBinder);
            // Reset original query, so that there is no Search param
            $xiagQuery = $newXiagQuery;
        }
        if ($hasSearch) {
            $queryBuilder->sortMeta('score', 'textScore');
        }
        return [
            'xiagQuery'     => $xiagQuery,
            'queryBuilder'  => $queryBuilder
        ];
    }

    /**
     * Check if collection has search indexes in DB
     *
     * @param string $prefix the prefix for custom text search indexes
     * @return bool
     */
    private function hasCustomSearchIndex($prefix = 'search')
    {
        $metadata = $this->repository->getClassMetadata();
        $indexes = $metadata->getIndexes();
        if (count($indexes) < 1) {
            return false;
        }
        $collectionsName = substr($metadata->getName(), strrpos($metadata->getName(), '\\') + 1);
        $searchIndexName = $prefix.$collectionsName.'Index';
        // We reverse as normally the search index is the last.
        foreach (array_reverse($indexes) as $index) {
            if (array_key_exists('keys', $index) && array_key_exists($searchIndexName, $index['keys'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Build Search text index
     *
     * @param MongoBuilder $queryBuilder Doctrine mongo query builder object
     * @param SearchNode   $searchNode   Graviton Search node
     * @return MongoBuilder
     */
    private function buildSearchTextQuery(MongoBuilder $queryBuilder, SearchNode $searchNode)
    {
        $searchArr = [];
        foreach ($searchNode->getSearchTerms() as $string) {
            if (!empty(trim($string))) {
                $searchArr[] = (strpos($string, '.') !== false) ? "\"{$string}\"" : $string;
            }
        }
        if (!empty($searchArr)) {
            $queryBuilder->addAnd($queryBuilder->expr()->text(implode(' ', $searchArr)));
        }
        return $queryBuilder;
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
        $this->manager->persist($entity);

        if ($doFlush) {
            $this->manager->flush($entity);
        }
        if ($returnEntity) {
            return $this->find($entity->getId());
        }
    }

    /**
     * @param string  $documentId id of entity to find
     * @param Request $request    request
     *
     * @return Object
     */
    public function find($documentId, Request $request = null)
    {
        if ($request instanceof Request) {
            // if we are provided a Request, we apply RQL

            /** @var MongoBuilder $queryBuilder */
            $queryBuilder = $this->repository
                ->createQueryBuilder();

            /** @var XiagQuery $query */
            $query = $request->attributes->get('rqlQuery');

            if ($query instanceof XiagQuery) {
                $queryBuilder = $this->doRqlQuery(
                    $queryBuilder,
                    $query
                );
            }

            $queryBuilder->field('id')->equals($documentId);

            $query = $queryBuilder->getQuery();
            return $query->getSingleResult();
        }

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
