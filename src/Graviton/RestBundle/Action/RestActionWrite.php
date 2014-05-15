<?php
namespace Graviton\RestBundle\Action;

use Symfony\Component\Validator\Constraints\Count;
use Graviton\RestBundle\Action\RestActionWriteInterface;
use Graviton\RestBundle\Response\ResponseFactory as Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//use Symfony\Component\HttpFoundation\Response;

/**
 * RestActionWrite
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class RestActionWrite implements RestActionWriteInterface
{
    private $doctrine;
    private $serializer;
    private $validator;
    private $router;
    
    /**
     * Constructor
     * 
     * @param Object $doctrine   Doctrine instance
     * @param Object $serializer Serializer instance
     * @param Object $validator  Validator instance
     * @param Object $router     Router instance
     */
    public function __construct($doctrine, $serializer, $validator, $router)
    {
        $this->doctrine = $doctrine;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->router = $router;
    }

    /**
     * (non-PHPdoc)
     * @see \Graviton\RestBundle\Action\RestActionWriteInterface::create()
     */
    public function create($request, $model)
    {
        $response = false;
        $record = $this->serializer->deserialize($request->getContent(), $model->getEntityClass(), 'json');
        
        $validationErrors = $this->validator->validate($record);
        
        if (count($validationErrors) > 0) {
            $response = Response::getResponse(400, $this->serializer->serialize($validationErrors, 'json'));
        }
        
        if (!$response) {
            $record = $model->insertRecord($record);
                        
            $response = Response::getResponse(201, $this->serializer->serialize($record, 'json'));
            
            $response->headers->set(
                'Location',
                'abc.de/'.$record->getId()
            );
        }
        
        
        return $response;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Graviton\RestBundle\Action\RestActionWriteInterface::update()
     */
    public function update($id, $request, $model)
    {
        $response = false;
        $record = $this->serializer->deserialize($request->getContent(), $model->getEntityClass(), 'json');
        
        $validationErrors = $this->validator->validate($record);
        
        if (count($validationErrors) > 0) {
            $response = Response::getResponse(400, $this->serializer->serialize($validationErrors, 'json'));
        }
        
        if (!$response) {
            $existingRecord = $model->find($id);
            if (!$existingRecord) {
                $response = Response::getResponse(
                    404,
                    $this->serializer->serialize(array('errors' => 'Entry with id '.$id.' not found'), 'json')
                );
            } else {
                $record = $model->updateRecord($id, $record);
        
                $response = Response::getResponse(200, $this->serializer->serialize($record, 'json'));
            }
        }
        
        return $response;
    }
    
    /**
     * (non-PHPdoc)
     * @see \Graviton\RestBundle\Action\RestActionWriteInterface::delete()
     */
    public function delete($id, $model)
    {
        $response = Response::getResponse(
            404,
            $this->serializer->serialize(array('errors' => 'Entry with id '.$id.' not found'), 'json')
        );
        
        if ($model->deleteRecord($id)) {
            $response = Response::getResponse(200);
        }

        return $response;
    }
}
