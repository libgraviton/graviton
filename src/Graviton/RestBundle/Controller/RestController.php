<?php

namespace Graviton\RestBundle\Controller;

use JMS\Serializer\Exception\Exception;
use JMS\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Graviton\SchemaBundle\SchemaUtils;
use Graviton\I18nBundle\Document\TranslatableDocumentInterface;
use Rs\Json\Patch;
use Graviton\ExceptionBundle\Exception\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Graviton\ExceptionBundle\Exception\NotFoundException;
use Graviton\ExceptionBundle\Exception\DeserializationException;
use Graviton\ExceptionBundle\Exception\SerializationException;

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
     * @var \Symfony\Component\DependencyInjection\ContainerInterface service_container
     */
    private $container;

    /**
     * {@inheritdoc}
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container service_container
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
     * @param string $id ID of record
     *
     * @return \Symfony\Component\HttpFoundation\Response $response Response with result or error
     */
    public function getAction($id)
    {
        $response = $this->container->get("graviton.rest.response");
        
        if (!($record = $this->getModel()->find($id))) {
            // looks like an exception factory... or builder
            $e = new NotFoundException("Entry with id ".$id.' not found!');
            $e->setResponse($response);
            throw $e;
        }
        
        $response->setStatusCode(Response::HTTP_OK);
        $response->setContent($this->serialize($record));
        
        return $response;
    }

    /**
     * Returns all records
     *
     * @return \Symfony\Component\HttpFoundation\Response $response Response with result or error
     */
    public function allAction()
    {
        $response = $this->container->get("graviton.rest.response");
        $response->setStatusCode(Response::HTTP_OK);
        $response->setContent($this->serialize($this->getModel()->findAll($this->getRequest())));
        
        return $response;
    }

    /**
     * Writes a new Entry to the database
     *
     * @return \Symfony\Component\HttpFoundation\Response $response Result of action with data (if successful)
     */
    public function postAction()
    {    
        // Get the response object from container
        $response = $this->container->get("graviton.rest.response");
        
        // Deserialize the request content (throws an exception if something fails)
        $record = $this->deserialize(
            $this->getRequest()->getContent(),
            $this->getModel()->getEntityClass()
        );

        // Re-validate the serialized record to make sure the serializer made no faults
        // Throws an exception if not
        $record = $this->validate($record);
        
        // Insert the new record
        $record = $this->getModel()->insertRecord($record);
        
        // store id of new record so we dont need to reparse body later when needed
        $this->getRequest()->attributes->set('id', $record->getId());
        
        // Set status code and content
        $response->setStatusCode(Response::HTTP_OK);
        $response->setContent($this->serialize($record));
        
        $routeParts = explode('.', $this->getRequest()->get('_route'));
        // remove last element (post in this case)
        array_pop($routeParts);
        // and replace it with get
        array_push($routeParts, 'get');
        
        $response->headers->set(
            'Location',
            $this->getRouter()->generate(implode('.', $routeParts), array('id' => $record->getId()))
        );

        return $response;
    }

    /**
     * Update a record
     *
     * @param Number $id ID of record
     *
     * @return \Symfony\Component\HttpFoundation\Response $response Result of action with data (if successful)
     */
    public function putAction($id)
    {
        $response = $this->container->get("graviton.rest.response");
        
        // If no record with this id exists, throw a not found exception
        if (!$this->getModel()->find($id)) {
            $e = new NotFoundException("Entry with id ".$id.' not found!');
            $e->setResponse($response);
            throw $e;
        }
        
        // Deserialize the content
        $record = $this->deserialize(
            $this->getRequest()->getContent(),
            $this->getModel()->getEntityClass()
        );
        
        // Re-validate the record
        $record = $this->validate($record);
        
        // And update the record, if everything is ok
        $record = $this->getModel()->updateRecord($id, $record);
        $response->setStatusCode(Response::HTTP_OK);
        $response->setContent($this->serialize($record));

        return $response;
    }

    /**
     * Deletes a record
     *
     * @param Number $id ID of record
     *
     * @return \Symfony\Component\HttpFoundation\Response $response Result of the action
     */
    public function deleteAction($id)
    {
        $response = $this->container->get("graviton.rest.response");
        
        // Does the record exist?
        if (!$this->getModel()->find($id)) {
            $e = new NotFoundException("Entry with id ".$id.' not found!');
            $e->setResponse($response);
            throw $e;
        }

        $this->getModel()->deleteRecord($id);
        $response->setStatusCode(Response::HTTP_OK);

        return $response;
    }

    /**
     * Patch a record (partial update) -> DO NOT USE THIS (or refactor it...)
     * We tried to implement the jsonpatch rfc but this is not possible
     * because of doctrine odm / serializer
     *
     * @param Number $id ID of record
     *
     * @return \Symfony\Component\HttpFoundation\Response $response Result of the action
     */
    public function patchAction($id)
    {
        $response = $this->container->get("graviton.rest.response");
        $response->setStatusCode(Response::HTTP_BAD_REQUEST);

        $record = $this->getModel()->find($id);

        // Get the patch params from request
        $requestContent = $this->getRequest()->getContent();

        if (!is_null($record) && !empty($requestContent)) {
            // get the record as json to handle json-patch
            $jsonString = $this->getSerializer()->serialize(
                $record,
                'json',
                $this->getSerializerContext()
            );

            // Now replace existing values with the new ones
            $patch = new Patch($jsonString, $requestContent);

            // Deserialize the new json string to an object
            $newRecord = $this->getSerializer()->deserialize(
                $patch->apply(),
                $this->getModel()->getEntityClass(),
                'json'
            );

            // If everything is ok, update record and return 204 No Content
            if ($this->validate($newRecord)) {
                $this->getModel()->updateRecord($id, $newRecord);
                $response->setStatusCode(Response::HTTP_NO_CONTENT);
            }
        }

        return $response;
    }

    /**
     * Return OPTIONS results.
     *
     * @param string $id ID of record
     *
     * @return \Symfony\Component\HttpFoundation\Response $response Result of the action
     */
    public function optionsAction($id = null)
    {
        $request = $this->getRequest();
        $request->attributes->set('schemaRequest', true);

        list($app, $module, , $modelName, $schemaType) = explode('.', $request->attributes->get('_route'));
        $model = $this->container->get(implode('.', array($app, $module, 'model', $modelName)));
        $document = $this->container->get(implode('.', array($app, $module, 'document', $modelName)));

        $translatableFields = array();
        if ($document instanceof TranslatableDocumentInterface) {
            $translatableFields = $document->getTranslatableFields();
        }
        $languages = array_map(
            function ($language) {
                return $language->getId();
            },
            $this->container->get('graviton.i18n.repository.language')->findAll()
        );

        $response = $this->container->get("graviton.rest.response");
        $response->setStatusCode(Response::HTTP_OK);
        //$response = $this->container->get('graviton.rest.response.200');
        $schemaMethod = 'getModelSchema';
        if (!$id && $schemaType != 'canonicalIdSchema') {
            $schemaMethod =  'getCollectionSchema';
        }
        $schema = SchemaUtils::$schemaMethod($modelName, $model, $translatableFields, $languages);
        $response->setContent(
            //$this->getSerializer()->serialize($schema, 'json')
            $this->serialize($schema)
        );

        // enabled methods for CorsListener
        $corsMethods = 'GET, POST, PUT, DELETE, OPTIONS';
        try {
            $router = $this->getRouter();
            // if post route is available we assume everything is readable
            $router->generate(implode('.', array($app, $module, 'rest', $modelName, 'post')));
        } catch (RouteNotFoundException $exception) {
            // only allow read methods
            $corsMethods = 'GET, OPTIONS';
        }
        $request->attributes->set('corsMethods', $corsMethods);

        return $response;
    }

    /**
     * Get request
     *
     * @return \Symfony\Component\HttpFoundation\Request $request Request object
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
     * @return \JMS\Serializer\Serializer\Serializer
     */
    public function getSerializer()
    {
        return $this->container->get('graviton.rest.serializer');
    }

    /**
     * Get the serializer context
     *
     * @return null|\JMS\Serializer\SerializationContext
     */
    public function getSerializerContext()
    {
        return clone $this->container->get('graviton.rest.serializer.serializercontext');
    }

    /**
     * Get the validator
     *
     * @return \Symfony\Component\Validator\Validator
     */
    public function getValidator()
    {
        return $this->container->get('graviton.rest.validator');
    }

    /**
     * Get the router from the dic
     *
     * @return \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    public function getRouter()
    {
        return $this->container->get('graviton.rest.router');
    }

    /**
     * Validate a record and throw a 400 error if not valid
     * ToDo: Find a bether name for this method
     *
     * @param Graviton\RestBundle\Model\DocumentModel $record Record
     *
     * @throws \Graviton\RestBundle\Controller\ValidationException
     *
     * @return boolean $ret true 
     */
    private function validate($record)
    {
        $response = $this->container->get("graviton.rest.response");
        
        // Re-validate record after serialization (we don't trust the serializer...)
        $violations = $this->getValidator()->validate($record);

        if ($violations->count() > 0) {
            $e = new ValidationException('Validation failed');
            $e->setViolations($violations);
            $e->setResponse($response);

            throw $e;
        }

        return $record;
    }

    /**
     * Serialize the given record and throw an exception if something went wrong
     * 
     * @param DocumentModel $record Record
     * 
     * @throws \Graviton\ExceptionBundle\Exception\SerializationException
     * 
     * @return string $content Json content
     */
    private function serialize($result)
    {
        $response = $this->container->get("graviton.rest.response");
        
        try {
            $content = $this->getSerializer()->serialize(
                $result,
                'json',
                $this->getSerializerContext()
            );
        } catch (\Exception $e) {
            $exception = new SerializationException();
            $exception->setResponse($response);
            throw $exception;
        }
        
        return $content;
    }
    
    /**
     * Deserialize the given content throw an exception if something went wrong
     * 
     * @param string $content       Request content
     * @param string $documentClass Document class
     * 
     * @throws \Graviton\ExceptionBundle\Exception\DeserializationException
     * 
     * @return object $record Document
     */
    private function deserialize($content, $documentClass)
    {
        $response = $this->container->get("graviton.rest.response");
        
        try {
            $record = $this->getSerializer()->deserialize(
                $content,
                $documentClass,
                'json'
            );
        } catch (\Exception $e) {
            // pass the previous exception in this case to get the error message in the handler
            // http://php.net/manual/de/exception.getprevious.php
            $exception = new DeserializationException("Deserialization failed", Response::HTTP_BAD_REQUEST, $e);
            
            // at the moment, the response has to be set on the exception object.
            // try to refactor this and return the graviton.rest.response if none is set...
            $exception->setResponse($response);
            throw $exception;
        }
        
        return $record;
    }
}
