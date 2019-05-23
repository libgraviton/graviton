<?php
/**
 * ModelInterface
 */

namespace Graviton\RestBundle\Model;

use Doctrine\ODM\MongoDB\DocumentRepository;
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
     * Set document repository
     *
     * @param DocumentRepository $repository document repo
     *
     * @return void
     */
    public function setRepository(DocumentRepository $repository);

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
     * @param object $entity       entity to insert
     * @param bool   $returnEntity true to return entity
     * @param bool   $doFlush      if we should flush or not after insert
     *
     * @return Object
     */
    public function insertRecord($entity, $returnEntity = true, $doFlush = true);

    /**
     * Update an existing entity
     *
     * @param string $documentId   id of entity to update
     * @param Object $entity       new entity
     * @param bool   $returnEntity true to return entity
     *
     * @return Object
     */
    public function updateRecord($documentId, $entity, $returnEntity = true);

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
