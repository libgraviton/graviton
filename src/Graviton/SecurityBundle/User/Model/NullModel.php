<?php
/**
 * null model
 */

namespace Graviton\SecurityBundle\User\Model;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Graviton\RestBundle\Model\ModelInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class NullModel
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class NullModel implements ModelInterface
{
    /**
     * @var DocumentRepository
     */
    private $repository;

    /**
     * Set document repository
     *
     * @param DocumentRepository $repository document repo
     *
     * @return void
     */
    public function setRepository(DocumentRepository $repository)
    {
        $this->repository = $repository;
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
        return [];
    }

    /**
     * Insert a new Record
     *
     * @param Request $request      request
     * @param object  $entity       entity to insert
     * @param bool    $returnEntity true to return entity
     * @param bool    $doFlush      if we should flush or not after insert
     *
     * @return Object
     */
    public function insertRecord(Request $request, $entity, $returnEntity = true, $doFlush = true)
    {
        return $this->find($entity->getId());
    }

    /**
     * Update an existing entity
     *
     * @param Request $request      request
     * @param string  $documentId   id of entity to update
     * @param Object  $entity       new entity
     * @param bool    $returnEntity true to return entity
     *
     * @return Object
     */
    public function updateRecord(Request $request, $documentId, $entity, $returnEntity = true)
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
