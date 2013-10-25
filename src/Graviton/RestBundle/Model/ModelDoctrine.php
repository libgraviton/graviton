<?php
namespace Graviton\RestBundle\Model;

use Graviton\RestBundle\Model\ModelInterface;

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
	private $entityClass;
	
	private $connectionName;
	
	private $doctrine;
	
	private $pager = false;
	
	private $parser = false;
	
	public function __construct($entityClass, $connectionName)
	{
		$this->entityClass = $entityClass;
		$this->connectionName = $connectionName;
	}
	
	public function find($id)
	{
		$em = $this->doctrine->getManager($this->connectionName);
		$result = $em->getRepository($this->entityClass)->find($id);
		
		return $result;
	}
	
	public function findAll()
	{
		$em = $this->doctrine->getManager($this->connectionName);
		
		$queryBuilder = $em->getRepository($this->entityClass)->createQueryBuilder('a');
		$query = $queryBuilder->getQuery();
		
		if ($this->parser) {
			//$query = $this->parser...
		}
		
		if ($this->pager) {
			$query->setFirstResult($this->pager->getOffset());
			$query->setMaxResults($pageSize->pager->getPageSize());
		}
		
		$result = $query->getResult();
		
		return $result;
	}
	
	public function insertRecord($entity)
	{
		$em = $this->doctrine->getManager($this->connectionName);
		
		$em->persist($entity);
		$em->flush();
		
		return $entity;
	}
	
	public function updateRecord($id, $entity)
	{
		$em = $this->doctrine->getManager($this->connectionName);
		
		$entity->setId($id);
		$entity = $em->merge($entity);
		$em->flush();
		
		return $entity;
	}
	
	public function deleteRecord($id)
	{
		$retVal = false;
		$entity = $this->find($id);
	
		if ($entity) {
			$em = $this->doctrine->getManager();
			$em->remove($entity);
			$em->flush();
			
			$retVal = true;
		}	
		var_dump($retVal);
		return $retVal;
	}
	
	public function getEntityClass()
	{
		return $this->entityClass;
	}
	
	public function getConnectionName()
	{
		return $this->connectionName;
	}
	
	public function setMapper($mapper)
	{
		$this->doctrine = $mapper;
	}
	
	public function setParser(RestParserInterface $parser)
	{
		$this->parser = $parser;
	}
	
	public function setPager(RestPagerInterface $pager)
	{
		$this->pager = $pager;
	}
}