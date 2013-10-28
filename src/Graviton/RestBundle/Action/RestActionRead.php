<?php
namespace Graviton\RestBundle\Action;

use Graviton\RestBundle\Action\RestActionReadInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Graviton\RestBundle\Response\ResponseFactory as Response;

/**
 * RestActionRead
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class RestActionRead implements RestActionReadInterface
{
	private $doctrine;
	private $serviceMapper;
	private $serializer;
	private $serializerContext = null;

	public function __construct($doctrine, $serviceMapper, $serializer, $serializerContext = null, $request = null)
	{
		$this->doctrine = $doctrine;
		$this->serviceMapper = $serviceMapper;
		$this->serializer = $serializer;
		$this->serializerContext = $serializerContext;
	}
	
	public function getOne($id, $request, $model)
	{
		$response = Response::getResponse(404, 'Entry with id '.$id.' not found');

		$result = $model->find($id);
		
		if ($result) {
			$response = Response::getResponse(200, $this->serializer->serialize($result, 'json', $this->serializerContext));
		}
		
		//add link header for each child
		//$url = $this->serviceMapper->get($entityClass, 'get', array('id' => $record->getId()));
		

		return $response;
	}
	
	public function getAll($request, $model)
	{
		$response = Response::getResponse(404);

		$result = $model->findAll();
		
		if ($result) {
			$response = Response::getResponse(200, $this->serializer->serialize($result, 'json', $this->serializerContext));
		}
		
		//add prev / next headers
		//$url = $this->serviceMapper->get($entityClass, 'get', array('id' => $record->getId()));

		return $response;
	}
}