<?php

namespace GravitonDyn\SecurityBundle\Tests\Fixtures\Model;

use Graviton\RestBundle\Model\DocumentModel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class Contract extends DocumentModel
{

    /**
     * this helps for fallback logic on outside classes to locate resources
     *
     * @var string
     */
    protected $_modelPath = __FILE__;

    /**
     * load some schema info for the model
     *
     * @param ContainerInterface $container Symfony's DIC
     *
     * @return \GravitonDyn\SecurityBundle\Tests\Fixtures\Model\Contract
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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

    /**+
     * Find all records
     *
     * @param Request $request Request object
     * @param array   $filter  AND clause to narrow result.
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
        return $this->find(42);
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
        return $entity;
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
        return;
    }
    /**
     * get classname of entity
     *
     * @return string
     */
    public function getEntityClass()
    {
        return 'TestContract';
    }
}
