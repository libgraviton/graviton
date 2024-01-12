<?php
/**
 * ModelInterface
 */

namespace Graviton\RestBundle\Model;

use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * ModelInterface
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
interface ModelInterface
{

    /**
     * get repository instance
     *
     * @return DocumentRepository
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
     * @param object $entity entity to insert
     *
     * @return Object
     */
    public function insertRecord($entity);

    /**
     * Update an existing entity
     *
     * @param string $documentId id of entity to update
     * @param Object $entity     new entity
     *
     * @return Object
     */
    public function updateRecord($documentId, $entity);

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
}
