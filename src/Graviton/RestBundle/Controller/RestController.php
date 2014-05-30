<?php

namespace Graviton\RestBundle\Controller;

use JMS\Serializer\Exception\Exception;
use JMS\Serializer\Serializer;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
class RestController implements ContainerAwareInterface
{
    private $model;

    /**
     * @var ContainerInteface service_container
     */
    private $container;

    /**
     * {@inheritdoc}
     *
     * @param ContainerInterface $container service_container
     *
     * @return void
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

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
                $this->getSerializer()->serialize($result, 'json', $this->getSerializerContext())
            );
        }

        //add link header for each child
        //$url = $this->router->get($entityClass, 'get', array('id' => $record->getId()));
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
        $result = $this->getModel()->findAll($this->container->get('request'));

        if ($result) {
            $baseName = basename(strtr($this->model->getEntityClass(), '\\', '/'));
            $serviceName = $this->model->getConnectionName().'.rest.'.strtolower($baseName);

            $response = Response::getResponse(
                200,
                $this->getSerializer()->serialize($result, 'json', $this->getSerializerContext())
            );
        }
        //add prev / next headers
        //$url = $this->serviceMapper->get($entityClass, 'get', array('id' => $record->getId()));
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

        // store id of new record so we dont need to reparse body later when needed
        $this->container->get('request')->attributes->set('id', $record->getId());

        $response = $this->validateRecord($record);

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

        $response = $this->validateRecord($record);

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

        return $response;
    }

    /**
     * Get request
     *
     * @return Request $request Request object
     */
    public function getRequest()
    {
        return $this->container->get('graviton.rest.request');
    }

    /**
     * Set the model class
     *
     * @param object $model Model class
     *
     * @return self
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
     * Get the serializer
     *
     * @return Serializer
     */
    public function getSerializer()
    {
        return $this->container->get('graviton.rest.serializer');
    }

    /**
     * Get the serializer context
     *
     * @return SerializationContext
     */
    public function getSerializerContext()
    {
        return $this->container->get('graviton.rest.serializer.serializercontext');
    }

    /**
     * Get the validator
     *
     * @return Validator
     */
    public function getValidator()
    {
        return $this->container->get('graviton.rest.validator');
    }

    /**
     * Get the router from the dic
     *
     * @return object
     */
    public function getRouter()
    {
        return $this->container->get('graviton.rest.router');
    }

    /**
     * validate a record and return an approriate reponse
     *
     * @param Object $record record to validate
     *
     * @return Response|null
     */
    private function validateRecord($record)
    {
        // override values from serializer with real ones from request to get originals validated
        foreach (json_decode($this->getRequest()->getContent()) as $key => $value) {
            $setterMethod = 'set'.ucfirst($key);
            $record->$setterMethod($value);
        }

        $validationErrors = $this->getValidator()->validate($record);

        $response =  null;
        if (count($validationErrors) > 0) {
            $response = Response::getResponse(400, $this->getSerializer()->serialize($validationErrors, 'json'));
        }

        return $response;
    }
}
