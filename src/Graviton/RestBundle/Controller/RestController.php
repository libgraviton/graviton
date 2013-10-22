<?php

namespace Graviton\RestBundle\Controller;


use FOS\RestBundle\Controller\FOSRestController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\View;
use Doctrine\ORM\Query;
use Graviton\RestBundle\Hydrator\DoctrineObject as DoctrineHydrator;

abstract class RestController extends Controller
{		
	private $restActionRead;
	private $restActionWrite;
	private $entityClass;
	private $connectionName = 'default';
	
	/**
	 * Returns single record
	 * 
	 * @param Number $id ID of record
	 * 
	 * @return Object $retVal Result from db
	 */
	public function getAction($id)
    {
    	return $this->restActionRead->getOne($id, $this->getRequest(),  'Financing\ExpiryListBundle\Entity\DataFinancingExpiry', 'report');
    }
    
    /**
     * Returns all records 
     * 
     * @return Object $retVal Collection of entries
     */
    public function allAction($page, $pageSize)
    {    	 
    	return $this->restActionRead->getAll($page, $pageSize, $this->getRequest(),  'Financing\ExpiryListBundle\Entity\DataFinancingExpiry', 'report');
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
		return $this->restActionWrite->write(null, $this->getRequest(),  'Financing\ExpiryListBundle\Entity\DataFinancingExpiry', 'report');
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
		return $this->restActionWrite->write($id, $this->getRequest(),  'Financing\ExpiryListBundle\Entity\DataFinancingExpiry', 'report');
    }
    
    /**
     * Deletes a record
     * 
     * @param Number $id ID of  record
     */
    public function deleteAction($id)
    {
    	$this->restActionWrite->delete($id);
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

    public function setEnityClass($entityClass)
    {
    	$this->entityClass = $entityClass;
    }
    
    public function setConnectionName($connection)
    {
    	$this->connection = $connection;
    }
}
