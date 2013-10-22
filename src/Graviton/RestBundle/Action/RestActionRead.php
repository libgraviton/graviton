<?php
namespace Graviton\RestBundle\Action;

use Graviton\RestBundle\Action\RestActionReadInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Graviton\RestBundle\Response\ResponseFactory as Response;

class RestActionRead implements RestActionReadInterface
{
	private $doctrine;
	private $serviceMapper;
	private $serializer;
	private $queryParser;

	public function __construct($doctrine, $serviceMapper, $serializer, $queryParser = null)
	{
		$this->doctrine = $doctrine;
		$this->serviceMapper = $serviceMapper;
		$this->serializer = $serializer;
		$this->queryParser = $queryParser;
	}
	
	public function getOne($id, $request, $entityClass, $connection)
	{
		$response = false;
		$result = false;
		$em = $this->doctrine->getManager($connection);
		
		$result = $em->getRepository($entityClass)->find($id);
		if (!$result) {
			throw new NotFoundHttpException('Entry with id '.$id.' not found');
		}
			
		//add link header for each child
		//$url = $this->serviceMapper->get($entityClass, 'get', array('id' => $record->getId()));
		$response = Response::getResponse(201, $this->serializer->serialize($result, 'json'));
		
		return $response;
	}
	
	public function getAll($page, $pageSize, $request, $entityClass, $connection)
	{
		$response = false;
		$result = false;
		$em = $this->doctrine->getManager($connection);
		
		$queryBuilder = $em->getRepository($entityClass)->createQueryBuilder('a');
		$query = $queryBuilder->getQuery();
		$offset = $page * $pageSize;
		
		$query->setFirstResult($offset);
		$query->setMaxResults($pageSize);
			
		//apply filter from parser
		//$queryBuilder = $this->queryParser->applyFilter($queryBuilder);
		
		$result = $query->getResult();
			
		//add prev / next headers
		//$url = $this->serviceMapper->get($entityClass, 'get', array('id' => $record->getId()));
			
		$response = Response::getResponse(201, $this->serializer->serialize($result, 'json'));
		
		return $response;
	}
}