<?php
/**
 * Use doctrine odm as backend
 */

namespace Graviton\RestBundle\Model;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Graviton\RestBundle\Service\RqlTranslator;
use Graviton\Rql\Node\SearchNode;
use Graviton\SchemaBundle\Model\SchemaModel;
use Graviton\SecurityBundle\Entities\SecurityUser;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ODM\MongoDB\Query\Builder;
use Graviton\Rql\Visitor\MongoOdm as Visitor;
use Xiag\Rql\Parser\Node\Query\LogicOperator\AndNode;
use Xiag\Rql\Parser\Node\Query\LogicOperator\OrNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\LikeNode;
use Xiag\Rql\Parser\Query;
use Graviton\ExceptionBundle\Exception\RecordOriginModifiedException;
use Xiag\Rql\Parser\Exception\SyntaxErrorException as RqlSyntaxErrorException;

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

        /** @var \Doctrine\ODM\MongoDB\Query\Builder $queryBuilder */
        $queryBuilder = $this->repository
            ->createQueryBuilder();

        if ($this->filterByAuthUser && $user && $user->hasRole(SecurityUser::ROLE_USER)) {
            $queryBuilder->field($this->filterByAuthField)->equals($user->getUser()->getId());
        }


        // *** do we have an RQL expression, do we need to filter data?
        if ($request->attributes->get('hasRql', false)) {
            $queryBuilder = $this->doRqlQuery(
                $queryBuilder,
                $this->translateSearchRql($request->attributes->get('rqlQuery'))
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
     * @param \Graviton\I18nBundle\Document\Translatable $entity entity to insert
     *
     * @return Object
     */
    public function insertRecord($entity)
    {
        $this->checkIfOriginRecord($entity);
        $manager = $this->repository->getDocumentManager();
        $manager->persist($entity);
        $manager->flush($entity);

        return $this->find($entity->getId());
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
     * @param string $documentId id of entity to update
     * @param Object $entity     new entity
     *
     * @return Object
     */
    public function updateRecord($documentId, $entity)
    {
        $manager = $this->repository->getDocumentManager();
        // In both cases the document attribute named originRecord must not be 'core'
        $this->checkIfOriginRecord($entity);
        $this->checkIfOriginRecord($this->find($documentId));
        $entity = $manager->merge($entity);
        $manager->flush();

        return $entity;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $documentId id of entity to delete
     *
     * @return null|Object
     */
    public function deleteRecord($documentId)
    {
        $manager = $this->repository->getDocumentManager();
        $entity = $this->find($documentId);

        $return = $entity;
        if ($entity) {
            $this->checkIfOriginRecord($entity);
            $manager->remove($entity);
            $manager->flush();
            $return = null;
        }

        return $return;
    }

    /**
     * get classname of entity
     *
     * @return string
     */
    public function getEntityClass()
    {
        return $this->repository->getDocumentName();
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

    /**
     * Kick the transformation of RQL Query
     *
     * @param Query $query Query to translate
     * @return Query
     */
    protected function translateSearchRql(Query $query)
    {
        $innerQuery = $query->getQuery();

        if ($innerQuery instanceof SearchNode) {
            $newNode = $this->translator->translateSearchNode($innerQuery, $this->getSearchableFields());

            if ($newNode instanceof OrNode) {
                $query->setQuery($newNode);
            }
        } elseif ($innerQuery instanceof AndNode) {
            $andNodeReplacement = new AndNode();
            foreach ($innerQuery->getQueries() as $innerNodeFromAnd) {

                if ($innerNodeFromAnd instanceof SearchNode) {
                    // Transform to OrNode with inner like queries and add to new query list
                    $andNodeReplacement->addQuery(
                        $this->translator->translateSearchNode($innerNodeFromAnd, $this->getSearchableFields())
                    );
                } else {
                    // Just recollect the node
                    $andNodeReplacement->addQuery($innerNodeFromAnd);
                }

            }

            $query->setQuery($andNodeReplacement);
        }


        return $query;
    }
}
