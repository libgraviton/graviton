<?php
/**
 * Use doctrine odm as backend
 *
 * PHP Version 5
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */

namespace Graviton\RestBundle\Model;

use Doctrine\Common\Persistence\ObjectRepository;
use Graviton\Rql\Queriable\MongoOdm;
use Graviton\Rql\Query;
use Graviton\SchemaBundle\Model\SchemaModel;
use Symfony\Component\HttpFoundation\Request;

/**
 * Use doctrine odm as backend
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
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
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    private $repository;

    /**
     * get repository instance
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * create new app model
     *
     * @param \Doctrine\Common\Persistence\ObjectRepository $countries Repository of countries
     *
     * @return void
     */
    public function setRepository(ObjectRepository $countries)
    {
        $this->repository = $countries;
    }

    /**
     * {@inheritDoc}
     *
     * @param \Symfony\Component\HttpFoundation\Request $request Request object
     *
     * @return array
     */
    public function findAll(Request $request)
    {
        $pageNumber = $request->query->get('page', 1);
        $numberPerPage = (int) $request->query->get(
            'perPage',
            $request->query->get('per_page', 10)
        );
        $startAt = ($pageNumber - 1) * $numberPerPage;

        // *** do we have an RQL expression, do we need to filter data?
        if (count($request->query->all()) > 0) {
            // prefer explicit filter param!
            if ($request->query->get('q') != null && strlen($request->query->get('q')) > 0) {
                $queryFilter = $request->query->get('q');
            } else {
                $queryFilter = $request->getQueryString();
            }

            $queryParser = new Query(urldecode($queryFilter));
            $queriable = new MongoOdm($this->repository, $numberPerPage, $startAt);
            $queriable = $queryParser->applyToQueriable($queriable);
            $records = $queriable->getDocuments();

            $totalCount = $queriable->getResultCount();
        } else {
            /** @var \Doctrine\ODM\MongoDB\Query\Builder $qb */
            $qb = $this->repository
                ->createQueryBuilder()
                ->limit($numberPerPage)
                ->find($this->repository->getDocumentName());

            /** @var \Doctrine\ODM\MongoDB\Query\Query $query */
            $query = $qb->getQuery();
            $totalCount = $query->count();
            $records = array_values(
                $query->execute()
                      ->toArray()
            );
        }

        $numPages = (int) ceil($totalCount / $numberPerPage);
        if ($numPages > 1) {
            $request->attributes->set('paging', true);
            $request->attributes->set('numPages', $numPages);
            $request->attributes->set('perPage', $numberPerPage);
        }

        return $records;
    }

    /**
     * {@inheritDoc}
     *
     * @param \Graviton\I18nBundle\Document\Translatable $entity entityy to insert
     *
     * @return Object
     */
    public function insertRecord($entity)
    {
        $manager = $this->repository->getDocumentManager();
        $manager->persist($entity);
        $manager->flush();

        return $this->find($entity->getId());
    }

    /**
     * {@inheritDoc}
     *
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
        $manager->persist($entity);
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
}
