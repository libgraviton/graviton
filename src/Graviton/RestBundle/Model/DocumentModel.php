<?php

namespace Graviton\RestBundle\Model;

use Doctrine\Common\Persistence\ObjectRepository;
use Knp\Component\Pager\Paginator;

/**
 * Use doctrine odm as backend
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class DocumentModel implements ModelInterface
{
    /**
     * @var ObjectRepository
     */
    private $repository;

    /**
     * @var Paginator
     */
    private $paginator;

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
     * create new app model
     *
     * @param ObjectRepository $countries Repository of countries
     *
     * @return void
     */
    public function setRepository(ObjectRepository $countries)
    {
        $this->repository = $countries;
    }

    /**
     * get repository instance
     *
     * @return ObjectRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * set paginator
     *
     * @param Paginator $paginator paginator used in collection
     *
     * @return void
     */
    public function setPaginator(Paginator $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $id id of entity to find
     *
     * @return Object
     */
    public function find($id)
    {
        return $this->repository->find($id);
    }

    /**
     * {@inheritDoc}
     *
     * @param Request $request Request object
     *
     * @return array
     */
    public function findAll($request)
    {
        $pagination = $this->paginator->paginate(
            $this->repository->findAll(),
            $request->query->get('page', 1),
            10
        );

        $numPages = (int) ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage());
        if ($numPages > 1) {
            $request->attributes->set('paging', true);
            $request->attributes->set('numPages', $numPages);
        }

        return $pagination->getItems();
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
        $dm = $this->repository->getDocumentManager();
        $dm->persist($entity);
        $dm->flush();

        return $this->find($entity->getId());
    }

    /**
     * {@inheritDoc}
     *
     * @param string $id     id of entity to update
     * @param Object $entity new enetity
     *
     * @return Object
     */
    public function updateRecord($id, $entity)
    {
        $dm = $this->repository->getDocumentManager();
        $dm->persist($entity);
        $dm->flush();

        return $entity;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $id id of entity to delete
     *
     * @return null|Object
     */
    public function deleteRecord($id)
    {
        $dm = $this->repository->getDocumentManager();
        $entity = $this->find($id);

        $return = $entity;
        if ($entity) {
            $dm->remove($entity);
            $dm->flush();
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

        return 'graviton.'.$bundle;
    }

    /**
     * get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * get title for a given field
     *
     * @param string $field field name
     *
     * @return string
     */
    public function getTitleOfField($field)
    {
        return $this->fieldTitles[$field];
    }

    /**
     * get description for a given field
     *
     * @param string $field field name
     *
     * @return string
     */
    public function getDescriptionOfField($field)
    {
        return $this->fieldDescriptions[$field];
    }

    /**
     * get required fields for this object
     *
     * @return string[]
     */
    public function getRequiredFields()
    {
        return $this->requiredFields;
    }
}
