<?php

namespace Graviton\RestBundle\Controller;

use JMS\Serializer\Exception\Exception;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Serializer;
use Graviton\RestBundle\Response\ResponseFactory as Response;

/**
 * This is a basic rest controller. It should fit the most needs but if you need to add some
 * extra functionality you can extend it and overwrite single/all actions.
 * You can also extend the model class to add some extra logic before save
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class RestController
{
    private $doctrine;
    private $request;
    private $validator;
    private $model;
    private $serializer;
    private $router;
    private $serializerContext = null;
    private $deserializerContext = null;

    /**
     * Returns a single record
     *
     * @param Number $id ID of record
     *
     * @return Response $response Response with result or error
     */
    public function getAction($id)
    {
        $response = Response::getResponse(404, 'Entry with id '.$id.' not found');
        $result = $this->getModel()->find($id);

        if ($result) {
            $response = Response::getResponse(
                200,
                $this->getSerializer()->serialize($result, 'json', $this->serializerContext)
            );
        }


        //add link header for each child
        //$url = $this->router->get($entityClass, 'get', array('id' => $record->getId()));

        // @todo refactor all vnd content type to come from a mapper
        $response->headers->set('Content-Type', 'application/vnd.graviton.core.app+json; charset=UTF-8');

        return $response;
    }

    /**
     * Returns all records
     *
     * @return Response $response Response with result or error
     */
    public function allAction()
    {
        $response = Response::getResponse(404);
        $result = $this->getModel()->findAll();

        if ($result) {
            $baseName = basename(strtr($this->model->getEntityClass(), '\\', '/'));
            $serviceName = $this->model->getConnectionName().'.rest.'.strtolower($baseName);

            $response = Response::getResponse(
                200,
                $this->getSerializer()->serialize($result, 'json', $this->serializerContext)
            );
        }
        //add prev / next headers
        //$url = $this->serviceMapper->get($entityClass, 'get', array('id' => $record->getId()));

        $response->headers->set('Content-Type', 'application/vnd.graviton.core.app+json; charset=UTF-8');

        return $response;
    }

    /**
     * Writes a new Entry to the database
     *
     * @return Response $response Result of action with data (if successful)
     */
    public function postAction()
    {
        $response = false;
        $record = $this->getSerializer()->deserialize(
            $this->getRequest()->getContent(),
            $this->getModel()->getEntityClass(),
            'json'
        );

        $validationErrors = $this->getValidator()->validate($record);

        if (count($validationErrors) > 0) {
            $response = Response::getResponse(400, $this->getSerializer()->serialize($validationErrors, 'json'));
        }

        if (!$response) {
            $baseName = basename(strtr($this->model->getEntityClass(), '\\', '/'));
            $serviceName = $this->model->getConnectionName().'.rest.'.strtolower($baseName);
            $record = $this->getModel()->insertRecord($record);
            $response = Response::getResponse(
                201,
                $this->getSerializer()->serialize($record, 'json'),
                array('Location' => $this->getRouter()->generate($serviceName.'.get', array('id' => $record->getId())))
            );
        }

        $response->headers->set('Content-Type', 'application/vnd.graviton.core.app+json; charset=UTF-8');

        return $response;
    }

    /**
     * Update a record
     *
     * @param Number $id ID of record
     *
     * @return Response $response Result of action with data (if successful)
     */
    public function putAction($id)
    {
        $response = false;
        $record = $this->getSerializer()->deserialize(
            $this->getRequest()->getContent(),
            $this->getModel()->getEntityClass(),
            'json'
        );

        $validationErrors = $this->getValidator()->validate($record);

        if (count($validationErrors) > 0) {
            $response = Response::getResponse(
                400,
                $this->getSerializer()->serialize($validationErrors, 'json')
            );
        }

        if (!$response) {
            $existingRecord = $this->getModel()->find($id);
            if (!$existingRecord) {
                $response = Response::getResponse(
                    404,
                    $this->getSerializer()->serialize(array('errors' => 'Entry with id '.$id.' not found'), 'json')
                );
            } else {
                $record = $this->getModel()->updateRecord($id, $record);
                $response = Response::getResponse(
                    200,
                    $this->getSerializer()->serialize($record, 'json', $this->getSerializerContext())
                );
            }
        }

        $response->headers->set('Content-Type', 'application/vnd.graviton.core.app+json; charset=UTF-8');

        return $response;
    }

    /**
     * Deletes a record
     *
     * @param Number $id ID of record
     *
     * @return Response $response Result of the action
     */
    public function deleteAction($id)
    {
        $response = Response::getResponse(
            404,
            $this->getSerializer()->serialize(array('errors' => 'Entry with id '.$id.' not found'), 'json')
        );

        if ($this->getModel()->deleteRecord($id)) {
            $response = Response::getResponse(200);
        }

        $response->headers->set('Content-Type', 'application/vnd.graviton.core.app+json; charset=UTF-8');

        return $response;
    }

    /**
     * Set the request object
     *
     * @param Request $request Request object
     *
     * @return RestController $this This Controller
     */
    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get request
     *
     * @return Request $request Request object
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set the model class
     *
     * @param object $model Model class
     *
     * @return
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Return the model
     *
     * @return object $model Model
     */
    public function getModel()
    {
        if (!$this->model) {
            throw new \Exception('No model is set for this controller');
        }

        return $this->model;
    }

    /**
     * Set doctrine
     *
     * @param Doctrine $doctrine Doctrine Object
     *
     * @return \Graviton\RestBundle\Controller\RestController
     */
    public function setDoctrine($doctrine)
    {
        $this->doctrine = $doctrine;

        return $this;
    }

    /**
     * Get doctrine
     *
     * @throws Exception
     *
     * @return Doctrine
     */
    public function getDoctrine()
    {
        if (!$this->doctrine) {
            throw new Exception('Doctrine is not set on this controller');
        }

        return $this->doctrine;
    }

    /**
     * Set the serializer
     *
     * @param Serializer $serializer  JMS serializer instance
     *
     * @return \Graviton\RestBundle\Controller\RestController
     */
    public function setSerializer(Serializer $serializer)
    {
        $this->serializer = $serializer;

        return $this;
    }

    /**
     * Get the serializer
     *
     * @return Serializer
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * Set serializer context
     *
     * @param SerializationContext $context Context
     *
     * @return \Graviton\RestBundle\Controller\RestController
     */
    public function setSerializerContext(SerializationContext $context)
    {
        $this->serializerContext = $context;

        return $this;
    }

    /**
     * Get the serializer context
     *
     * @return SerializationContext
     */
    public function getSerializerContext()
    {
        return $this->serializerContext;
    }

    /**
     * Set deserializer context
     *
     * @param DeserializationContext $context context
     *
     * @return \Graviton\RestBundle\Controller\RestController
     */
    public function setDeserializerContext(DeserializationContext $context)
    {
        $this->deserializerContext = $context;

        return $this;
    }

    /**
     * Get deserializer context
     *
     * @return DeserializationContext
     */
    public function getDeserializerContext()
    {
        return $this->deserializerContext;
    }

    /**
     * Set validator
     *
     * @param Validator $validator Validator instance
     *
     * @return \Graviton\RestBundle\Controller\RestController
     */
    public function setValidator($validator)
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * Get the validator
     *
     * @throws Exception
     *
     * @return Validator
     */
    public function getValidator()
    {
        if (!$this->validator) {
            throw new Exception('No validator set on this controller');
        }

        return $this->validator;
    }

    public function setRouter($router)
    {
        $this->router = $router;
    }

    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Litte helper to add a serializer context which add null serialization
     *
     * @param boolean $serializeNull do it or not
     *
     * @return \Graviton\RestBundle\Controller\RestController
     */
    public function setSerializeNull($serializeNull = true)
    {
        if (true === $serializeNull) {
            if (!$this->getSerializerContext() instanceof SerializationContext) {
                $context = new SerializationContext();
                $context->setSerializeNull(true);
                $this->setSerializerContext($context);
            } else {
                $this->getSerializerContext()->setSerializeNull(true);
            }
        }

        return $this;
    }

    /**
     * Litte helper to add a deserialization context which add null serialization
     * Don't know if it's necessary, but maybe it influences the validator (code one sould read...)
     *
     * @param boolean $deserializeNull DO it or not
     *
     * @return \Graviton\RestBundle\Controller\RestControlle
     */
    public function setDeserializeNull($deserializeNull = true)
    {
        if (true === $deserializeNull) {
            if (!$this->getDeserializerContext() instanceof DeserializationContext) {
                $context = new DeserializationContext();
                $context->setSerializeNull(true);
                $this->setDeserializerContext($context);
            } else {
                $this->getDeserializerContext()->setSerializeNull(true);
            }
        }

        return $this;
    }
}
