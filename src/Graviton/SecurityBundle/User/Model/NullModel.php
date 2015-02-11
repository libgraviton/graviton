<?php
/**
 * null model
 */

namespace Graviton\SecurityBundle\User\Model;

use Doctrine\Common\Persistence\ObjectRepository;
use Graviton\RestBundle\Model\ModelInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class NullModel
 *
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class NullModel implements ModelInterface
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    private $repository;

    /**
     * Set document repository
     *
     * @param \Doctrine\Common\Persistence\ObjectRepository $repository document repo
     *
     * @return void
     */
    public function setRepository(ObjectRepository $repository)
    {
        $this->repository = $repository;
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
     * Find a single record by id
     *
     * @param string $id Record-Id
     *
     * @return Object
     */
    public function find($id)
    {
        return;
    }

    /**
     * Find all records
     *
     * @param \Symfony\Component\HttpFoundation\Request $request Request object
     *
     * @return Object[]
     */
    public function findAll(Request $request)
    {
        return array();
    }

    /**
     * Insert a new Record
     *
     * @param Object $entity Entity
     *
     * @return Object
     */
    public function insertRecord($entity)
    {
        return $this->find($entity->getId());
    }

    /**
     * Update an existing entity
     *
     * @param string $id     id of entity to update
     * @param Object $entity entity with new data
     *
     * @return Object
     */
    public function updateRecord($id, $entity)
    {
        return;
    }

    /**
     * Delete a record by id
     *
     * @param Number $id Record-Id
     *
     * @return null|Object
     */
    public function deleteRecord($id)
    {
        return null;
    }

    /**
     * Get the name of entity class
     *
     * @return string
     */
    public function getEntityClass()
    {
        return '';
    }

    /**
     * Get the connection name
     *
     * @return string
     */
    public function getConnectionName()
    {
        return '';
    }
}
