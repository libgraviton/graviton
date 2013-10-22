<?php
namespace Graviton\RestBundle\Action;

use Symfony\Component\Validator\Constraints\Count;

use Graviton\RestBundle\Action\RestActionWriteInterface;
use Graviton\RestBundle\Response\ResponseFactory as Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
//use Symfony\Component\HttpFoundation\Response;

class RestActionWrite implements RestActionWriteInterface
{
	private $doctrine;
	private $serializer;
	private $validator;
	private $router;
	
	public function __construct($doctrine, $serializer, $validator, $router)
	{
		$this->doctrine = $doctrine;
		$this->serializer = $serializer;
		$this->validator = $validator;
		$this->router = $router;
	}

	public function write($id, $request, $entityClass, $connection)
	{
		$response = false;
		
		// deserialize from post
		$newRecord = $this->serializer->deserialize($request->getContent(), $entityClass, 'json');
				
		//validate the new record
		$validationErrors = $this->validator->validate($newRecord);

		if (count($validationErrors) > 0) {
			$response = Response::getResponse(400, $this->serializer->serialize($validationErrors, 'json'));
		}

		//get em and repository
		$em = $this->doctrine->getManager($connection);
		$repository = $em->getRepository($entityClass);
		//$repository = $this->doctrine->getRepository($entityClass, $connection);
		
		//if an id is set, we need to update the record
		if(!$response) {
			if (null != $id && 0 < $id) {
				if (!$repository->find($id)) {
					$response = Response::getResponse(404, '');
				} else {
					$newRecord->setId($id);
							
					$newRecord = $em->merge($newRecord);
					$em->flush();
							
					$response = Response::getResponse(204, '');
				}
				$newRecord->setId($id);
				
				$newRecord = $em->merge($newRecord);
				$em->flush();
				
				$response = Response::getResponse(204, '');
			} else {
				$em->persist($newRecord);
				$em->flush();
				
				//get classname of Entity
				$classname = get_class($newRecord);				
				if (preg_match('@\\\\([\w]+)$@', $classname, $matches)) {
					$classname = $matches[1];
				}
				
				$url = $connection.'_'.lcfirst($classname).'_get';
				
				$response = Response::getResponse(201, '');
				$response->headers->set(
					'Location', $this->router->generate(
						$url, array('id' => $newRecord->getId()),
						true
					)
				);
			}
		}

		return $response;
	}
	
	public function delete($id, $modelClass, $connection)
	{
		$retVal = false;
		
		$model = $this->repository->find($id);
		if (!$model) {
			throw new NotFoundHttpException('Entry with id '.$id.' not found');
		}
		
		$em = $this->repository->getManager();
		$em->remove($model);
		$em->flush();
		
		
	}
}