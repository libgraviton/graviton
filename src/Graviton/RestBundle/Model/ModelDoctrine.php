<?php
namespace Graviton\RestBundle\Model;

use Graviton\RestBundle\Model\ModelInterface;
use Graviton\RestBundle\Pager;

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
	 * Pager instance
	 * 
	 * @var PagerInterface
	 */
	private $pager = false;
	
	/**
	 * Parser instance
	 * 
	 * @var ParserInterface
	 */
	private $parser = false;
	
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
	 * (non-PHPdoc)
	 * @see \Graviton\RestBundle\Model\ModelInterface::find()
	 */
	public function find($id)
	{
		$em = $this->doctrine->getManager($this->connectionName);
		$result = $em->getRepository($this->entityClass)->find($id);
		
		return $result;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Graviton\RestBundle\Model\ModelInterface::findAll()
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
	 * (non-PHPdoc)
	 * @see \Graviton\RestBundle\Model\ModelInterface::insertRecord()
	 */
	public function insertRecord($entity)
	{
		$em = $this->doctrine->getManager($this->connectionName);
		
		$em->persist($entity);
		$em->flush();
		
		return $entity;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Graviton\RestBundle\Model\ModelInterface::updateRecord()
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
	 * (non-PHPdoc)
	 * @see \Graviton\RestBundle\Model\ModelInterface::deleteRecord()
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
	 * (non-PHPdoc)
	 * @see \Graviton\RestBundle\Model\ModelInterface::getEntityClass()
	 */
	public function getEntityClass()
	{
		return $this->entityClass;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Graviton\RestBundle\Model\ModelInterface::getConnectionName()
	 */
	public function getConnectionName()
	{
		return $this->connectionName;
	}
	
	/**
	 *
	 * @return \Graviton\RestBundle\Model\PagerInterface
	 */
	public function getPager()
	{
		return $this->pager;
	}
	
	public function getParser()
	{
		return $this->parser;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Graviton\RestBundle\Model\ModelInterface::setMapper()
	 */
	public function setDoctrine($doctrine)
	{
		$this->doctrine = $doctrine;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Graviton\RestBundle\Model\ModelInterface::setParser()
	 */
	public function setParser($parser)
	{
		$this->parser = $parser;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Graviton\RestBundle\Model\ModelInterface::setPager()
	 */
	public function setPager($pager)
	{
		$this->pager = $pager;
	}
}