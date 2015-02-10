<?php
/**
 * ModelInterface
 */

namespace Graviton\RestBundle\Model;

use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * ModelInterface
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
interface ModelInterface
{
    /**
     * Set document repository
     *
     * @param \Doctrine\Common\Persistence\ObjectRepository $repository document repo
     *
     * @return void
     */
    public function setRepository(ObjectRepository $repository);

    /**
     * get repository instance
     *
     * @return ObjectRepository
     */
    public function getRepository();

    /**
     * Find a single record by id
     *
     * @param string $id Record-Id
     *
     * @return Object
     */
    public function find($id);

    /**
     * Find all records
     *
     * @param \Symfony\Component\HttpFoundation\Request $request Request object
     *
     * @return Object[]
     */
    public function findAll(Request $request);

    /**
     * Insert a new Record
     *
     * @param Object $entity Entity
     *
     * @return Object
     */
    public function insertRecord($entity);

    /**
     * Update an existing entity
     *
     * @param string $id     id of entity to update
     * @param Object $entity entity with new data
     *
     * @return Object
     */
    public function updateRecord($id, $entity);

    /**
     * Delete a record by id
     *
     * @param Number $id Record-Id
     *
     * @return null|Object
     */
    public function deleteRecord($id);

    /**
     * Get the name of entity class
     *
     * @return string
     */
    public function getEntityClass();

    /**
     * Get the connection name
     *
     * @return string
     */
    public function getConnectionName();
}
