<?php

namespace Graviton\RestBundle\Controller;


use FOS\RestBundle\Controller\FOSRestController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\View;
use Doctrine\ORM\Query;
use Graviton\RestBundle\Hydrator\DoctrineObject as DoctrineHydrator;

/**
 * RestController
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
abstract class RestController extends Controller
{		
	private $restActionRead;
	private $restActionWrite;
	private $model;
	private $request;
	
	/**
	 * Returns single record
	 * 
	 * @param Number $id ID of record
	 * 
	 * @return Object $retVal Result from db
	 */
	public function getAction($id)
    {
    	return $this->restActionRead->getOne($id, $this->request, $this->model);
    }
    
    /**
     * Returns all records 
     * 
     * @return Object $retVal Collection of entries
     */
    public function allAction()
    {    	 
    	return $this->restActionRead->getAll($this->request, $this->model);
    }
    
    /**
     * Writes a new Entry to the Database
     * 
     * @param Post $post Post params
     * 
     * @return
     */
    public function postAction()
    {    		
		return $this->restActionWrite->create($this->request, $this->model);
    }
    
    /**
     * Update a record
     *  
     * @param Number $id ID of record
     * 
     * @return 
     */
    public function putAction($id)
    {   	
		return $this->restActionWrite->update($id, $this->request, $this->model);
    }
    
    /**
     * Deletes a record
     * 
     * @param Number $id ID of  record
     */
    public function deleteAction($id)
    {
    	return $this->restActionWrite->delete($id, $this->model);
    }
    
    /**
     * Get request
     *
     * @param Number $id ID of  record
     */
    public function getRequest()
    {
    	return $this->request;
    }
    
    /**
     * Setter for read-action
     * 
     * @param RestActionReadInterface $action Read action
     * 
     * @return void
     */
    public function setRestActionRead($action)
    {
    	$this->restActionRead = $action;
    	
    	return;
    }
    
    /**
     * Setter for write-action
     * 
     * @param RestActionReadInterface $action Write action
     * 
     * @return void
     */
    public function setRestActionWrite($action)
    {
    	$this->restActionWrite = $action;
    	
    	return;
    }
    
    public function setModel($model)
    {
    	$this->model = $model;
    }
    
    public function setRequest($request)
    {
    	$this->request = $request;
    }
}
