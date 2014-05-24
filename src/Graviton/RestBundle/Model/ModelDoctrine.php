<?php
namespace Graviton\RestBundle\Model;

/**
 * ModelDoctrine
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class ModelDoctrine implements ModelInterface
{
    /**
     * Entity / Document name (MyBundle\Entity\Test)
     *
     * @var String
     */
    private $entityClass;

    /**
     * Connection name of this entity/document
     *
     * @var String
     */
    private $connectionName;

    /**
     * Doctrine instance
     *
     * @var unknown_type
     */
    private $doctrine;

    /**
     * Constructor
     *
     * @param String $entityClass    Entity class
     * @param String $connectionName Connection name
     *
     * @return void
     */
    public function __construct($entityClass, $connectionName)
    {
        $this->entityClass = $entityClass;
        $this->connectionName = $connectionName;
    }

    /**
     * {@inheritDoc}
     *
     * @param String $id id of entity to find
     *
     * @see \Graviton\RestBundle\Model\ModelInterface::find()
     *
     * @return Object
     */
    public function find($id)
    {
        $em = $this->doctrine->getManager($this->connectionName);
        $result = $em->getRepository($this->entityClass)->find($id);

        return $result;
    }

    /**
     * {@inheritDoc}
     *
     * @see \Graviton\RestBundle\Model\ModelInterface::findAll()
     *
     * @return Array
     */
    public function findAll()
    {
        $em = $this->doctrine->getManager($this->connectionName);

        $queryBuilder = $em->getRepository($this->entityClass)->createQueryBuilder('a');

        if ($this->parser) {
            $this->parser->parse(array());
            //$visitor ...
        }
        $query = $queryBuilder->getQuery();

        // get totaL count
        $queryBuilder->select(array('count(a.id)'));
        $countQuery = $queryBuilder->getQuery();
        $total = $countQuery->getSingleScalarResult();

        if ($this->pager) {
            $this->pager->setTotalCount($total);
            $query->setFirstResult($this->pager->getOffset());
            $query->setMaxResults($this->pager->getPageSize());
        }

        $result = $query->getResult();

        return $result;
    }

    /**
     * {@inheritDoc}
     *
     * @param Object $entity new entity to insert
     *
     * @see \Graviton\RestBundle\Model\ModelInterface::insertRecord()
     *
     * @return Object
     */
    public function insertRecord($entity)
    {
        $em = $this->doctrine->getManager($this->connectionName);

        $em->persist($entity);
        $em->flush();

        return $entity;
    }

    /**
     * {@inheritDoc}
     *
     * @param String $id     id of entity to update
     * @param Object $entity entity with updated values
     *
     * @see \Graviton\RestBundle\Model\ModelInterface::updateRecord()
     *
     * @return Object
     */
    public function updateRecord($id, $entity)
    {
        $em = $this->doctrine->getManager($this->connectionName);

        $entity->setId($id);
        $entity = $em->merge($entity);
        $em->flush();

        return $entity;
    }

    /**
     * {@inheritDoc}
     *
     * @param String $id id of entity to delete
     *
     * @see \Graviton\RestBundle\Model\ModelInterface::deleteRecord()
     *
     * @return Boolean
     */
    public function deleteRecord($id)
    {
        $retVal = false;
        $entity = $this->find($id);

        if ($entity) {
            $em = $this->doctrine->getManager($this->connectionName);
            $em->remove($entity);
            $em->flush();

            $retVal = true;
        }

        return $retVal;
    }

    /**
     * {@inheritDoc}
     *
     * @see \Graviton\RestBundle\Model\ModelInterface::getEntityClass()
     *
     * @return String
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * {@inheritDoc}
     *
     * @see \Graviton\RestBundle\Model\ModelInterface::getConnectionName()
     *
     * @return String
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * {@inheritDoc}
     *
     * @param Doctrine $doctrine doctrine factory
     *
     * @see \Graviton\RestBundle\Model\ModelInterface::setMapper()
     *
     * @return void
     */
    public function setDoctrine($doctrine)
    {
        $this->doctrine = $doctrine;
    }
}
