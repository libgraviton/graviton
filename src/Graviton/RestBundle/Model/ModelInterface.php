<?php
namespace Graviton\RestBundle\Model;

/**
 * ModelInterface
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
interface ModelInterface
{
	/**
	 * Find a single record by id
	 * 
	 * @param Number $id Record-Id
	 */
	public function find($id);
	
	/**
	 * Find all records
	 */
	public function findAll();
	
	/**
	 * Insert a new Record
	 * 
	 * @param Object $entity Entity
	 */
	public function insertRecord($entity);
	
	/**
	 * Update an existing entity
	 * 
	 * @param Object $entity Entity
	 */
	public function updateRecord($id, $entity);
	
	/**
	 * Delete a record by id
	 * 
	 * @param Number $id Record-Id
	 */
	public function deleteRecord($id);
	
	/**
	 * Get the name of entity class
	 */
	public function getEntityClass();
	
	/**
	 * Get the connection name
	 */
	public function getConnectionName();
}