<?php
/**
 * basic rest controller
 */

namespace Graviton\RestBundle\Controller;

use Graviton\DocumentBundle\Service\CollectionCache;
use Graviton\ExceptionBundle\Exception\InvalidJsonPatchException;
use Graviton\ExceptionBundle\Exception\MalformedInputException;
use Graviton\ExceptionBundle\Exception\SerializationException;
use Graviton\RestBundle\Model\DocumentModel;
use Graviton\RestBundle\Service\RestUtils;
use Graviton\SchemaBundle\SchemaUtils;
use Graviton\SecurityBundle\Service\SecurityUtils;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Rs\Json\Patch;
use Graviton\RestBundle\Service\JsonPatchValidator;

/**
 * This is a basic rest controller. It should fit the most needs but if you need to add some
 * extra functionality you can extend it and overwrite single/all actions.
 * You can also extend the model class to add some extra logic before save
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class RestController
{
    /**
     * @var DocumentModel
     */
    private $model;

    /**
     * @var ContainerInterface service_container
     */
    private $container;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var SchemaUtils
     */
    private $schemaUtils;

    /**
     * @var RestUtils
     */
    protected $restUtils;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var JsonPatchValidator
     */
    private $jsonPatchValidator;

    /**
     * @var SecurityUtils
     */
    protected $securityUtils;

    /** @var CollectionCache */
    protected $collectionCache;

    /**
     * @param Response           $response    Response
     * @param RestUtils          $restUtils   Rest Utils
     * @param Router             $router      Router
     * @param EngineInterface    $templating  Templating
     * @param ContainerInterface $container   Container
     * @param SchemaUtils        $schemaUtils Schema utils
     * @param CollectionCache    $cache       Cache service
     * @param JsonPatchValidator $jsonPatch   Service for validation json patch
     * @param SecurityUtils      $security    The securityUtils service
     */
    public function __construct(
        Response $response,
        RestUtils $restUtils,
        Router $router,
        EngineInterface $templating,
        ContainerInterface $container,
        SchemaUtils $schemaUtils,
        CollectionCache $cache,
        JsonPatchValidator $jsonPatch,
        SecurityUtils $security
    ) {
        $this->response = $response;
        $this->restUtils = $restUtils;
        $this->router = $router;
        $this->templating = $templating;
        $this->container = $container;
        $this->schemaUtils = $schemaUtils;
        $this->collectionCache = $cache;
        $this->jsonPatchValidator = $jsonPatch;
        $this->securityUtils = $security;
    }

    /**
     * Get the container object
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     *
     * @obsolete
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Returns a single record
     *
     * @param Request $request Current http request
     * @param string  $id      ID of record
     *
     * @return \Symfony\Component\HttpFoundation\Response $response Response with result or error
     */
    public function getAction(Request $request, $id)
    {
        $document = $this->getModel()->getSerialised($id, $request);

        $response = $this->getResponse()
            ->setStatusCode(Response::HTTP_OK)
            ->setContent($document);

        return $response;
    }

    /**
     * Get the response object
     *
     * @return \Symfony\Component\HttpFoundation\Response $response Response object
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Return the model
     *
     * @throws \Exception in case no model was defined.
     *
     * @return DocumentModel $model Model
     */
    public function getModel()
    {
        if (!$this->model) {
            throw new \Exception('No model is set for this controller');
        }

        return $this->model;
    }

    /**
     * Set the model class
     *
     * @param DocumentModel $model Model class
     *
     * @return self
     */
    public function setModel(DocumentModel $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Returns all records
     *
     * @param Request $request Current http request
     *
     * @return \Symfony\Component\HttpFoundation\Response $response Response with result or error
     */
    public function allAction(Request $request)
    {
        $model = $this->getModel();

        $content = $this->restUtils->serialize($model->findAll($request));

        $response = $this->getResponse()
            ->setStatusCode(Response::HTTP_OK)
            ->setContent($content);

        return $response;
    }

    /**
     * Writes a new Entry to the database
     *
     * @param Request $request Current http request
     *
     * @return \Symfony\Component\HttpFoundation\Response $response Result of action with data (if successful)
     */
    public function postAction(Request $request)
    {
        // Get the response object from container
        $response = $this->getResponse();
        $model = $this->getModel();

        $this->restUtils->checkJsonRequest($request, $response, $this->getModel());

        $record = $this->restUtils->validateRequest($request->getContent(), $model);

        // Insert the new record
        $record = $model->insertRecord($record);

        // store id of new record so we dont need to reparse body later when needed
        $request->attributes->set('id', $record->getId());

        // Set status code
        $response->setStatusCode(Response::HTTP_CREATED);

        $response->headers->set(
            'Location',
            $this->getRouter()->generate($this->restUtils->getRouteName($request), array('id' => $record->getId()))
        );

        return $response;
    }

    /**
     * Get the router from the dic
     *
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Update a record
     *
     * @param Number  $id      ID of record
     * @param Request $request Current http request
     *
     * @throws MalformedInputException
     *
     * @return Response $response Result of action with data (if successful)
     */
    public function putAction($id, Request $request)
    {
        $response = $this->getResponse();
        $model = $this->getModel();
        $repository = $model->getRepository();

        // Check and wait if another update is being processed
        $this->collectionCache->updateOperationCheck($repository, $id);
        $this->collectionCache->addUpdateLock($repository, $id);

        try {
            // Any of the following can throw a not valid json or data. So we release the lock.
            $this->restUtils->checkJsonRequest($request, $response, $this->getModel());
            $record = $this->restUtils->validateRequest($request->getContent(), $model);
        } catch (\Exception $e) {
            $this->collectionCache->releaseUpdateLock($repository, $id);
            throw $e;
        }

        // handle missing 'id' field in input to a PUT operation
        // if it is settable on the document, let's set it and move on.. if not, inform the user..
        if ($record->getId() != $id) {
            // try to set it..
            if (is_callable(array($record, 'setId'))) {
                $record->setId($id);
            } else {
                $this->collectionCache->releaseUpdateLock($repository, $id);
                throw new MalformedInputException('No ID was supplied in the request payload.');
            }
        }

        // And update the record, if everything is ok
        if (!$this->getModel()->recordExists($id)) {
            $this->getModel()->insertRecord($record, false);
        } else {
            $this->getModel()->updateRecord($id, $record, false);
        }

        $this->collectionCache->releaseUpdateLock($repository, $id);

        // Set status code
        $response->setStatusCode(Response::HTTP_NO_CONTENT);

        // store id of new record so we dont need to reparse body later when needed
        $request->attributes->set('id', $record->getId());

        return $response;
    }

    /**
     * Patch a record
     *
     * @param Number  $id      ID of record
     * @param Request $request Current http request
     *
     * @throws MalformedInputException
     *
     * @return Response $response Result of action with data (if successful)
     */
    public function patchAction($id, Request $request)
    {
        $response = $this->getResponse();
        $model = $this->getModel();

        // Check JSON Patch request
        $this->restUtils->checkJsonRequest($request, $response, $model);
        $this->restUtils->checkJsonPatchRequest(json_decode($request->getContent(), 1));

        // Check and wait if another update is being processed
        $this->collectionCache->updateOperationCheck($model->getRepository(), $id);

        // Find record && apply $ref converter
        $jsonDocument = $model->getSerialised($id);
        $this->collectionCache->addUpdateLock($model->getRepository(), $id);

        try {
            // Check if valid
            $this->jsonPatchValidator->validate($jsonDocument, $request->getContent());

            // Apply JSON patches
            $patch = new Patch($jsonDocument, $request->getContent());
            $patchedDocument = $patch->apply();
        } catch (\Exception $e) {
            $this->collectionCache->releaseUpdateLock($model->getRepository(), $id);
            throw new InvalidJsonPatchException($e->getMessage());
        }

        // Validation don't check for not valid path HTTP_NOT_MODIFIED, so if no change done, notify.
        if ($jsonDocument == $patchedDocument) {
            $response->setStatusCode(Response::HTTP_NOT_MODIFIED);
            return $response;
        }

        // Validate result object
        $model = $this->getModel();
        $record = $this->restUtils->validateRequest($patchedDocument, $model);

        // Update object
        $this->getModel()->updateRecord($id, $record);

        $this->collectionCache->releaseUpdateLock($model->getRepository(), $id);

        // Set status code
        $response->setStatusCode(Response::HTTP_OK);

        // Set Content-Location header
        $response->headers->set(
            'Content-Location',
            $this->getRouter()->generate($this->restUtils->getRouteName($request), array('id' => $record->getId()))
        );

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
        $response = $this->getResponse();
        $this->model->deleteRecord($id);
        $response->setStatusCode(Response::HTTP_NO_CONTENT);

        return $response;
    }

    /**
     * Return OPTIONS results.
     *
     * @param Request $request Current http request
     *
     * @throws SerializationException
     * @return \Symfony\Component\HttpFoundation\Response $response Result of the action
     */
    public function optionsAction(Request $request)
    {
        list($app, $module, , $modelName) = explode('.', $request->attributes->get('_route'));

        $response = $this->response;
        $response->setStatusCode(Response::HTTP_NO_CONTENT);

        // enabled methods for CorsListener
        $corsMethods = 'GET, POST, PUT, PATCH, DELETE, OPTIONS';
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
     * Return schema GET results.
     *
     * @param Request $request Current http request
     * @param string  $id      ID of record
     *
     * @throws SerializationException
     * @return \Symfony\Component\HttpFoundation\Response $response Result of the action
     */
    public function schemaAction(Request $request, $id = null)
    {
        $request->attributes->set('schemaRequest', true);

        list($app, $module, , $modelName, $schemaType) = explode('.', $request->attributes->get('_route'));

        $response = $this->response;
        $response->setStatusCode(Response::HTTP_OK);
        $response->setPublic();

        if (!$id && $schemaType != 'canonicalIdSchema') {
            $schema = $this->schemaUtils->getCollectionSchema($modelName, $this->getModel());
        } else {
            $schema = $this->schemaUtils->getModelSchema($modelName, $this->getModel());
        }

        // enabled methods for CorsListener
        $corsMethods = 'GET, POST, PUT, PATCH, DELETE, OPTIONS';
        try {
            $router = $this->getRouter();
            // if post route is available we assume everything is readable
            $router->generate(implode('.', array($app, $module, 'rest', $modelName, 'post')));
        } catch (RouteNotFoundException $exception) {
            // only allow read methods
            $corsMethods = 'GET, OPTIONS';
        }
        $request->attributes->set('corsMethods', $corsMethods);


        return $this->render(
            'GravitonRestBundle:Main:index.json.twig',
            ['response' => $this->restUtils->serialize($schema)],
            $response
        );
    }

    /**
     * Renders a view.
     *
     * @param string   $view       The view name
     * @param array    $parameters An array of parameters to pass to the view
     * @param Response $response   A response instance
     *
     * @return Response A Response instance
     */
    public function render($view, array $parameters = array(), Response $response = null)
    {
        return $this->templating->renderResponse($view, $parameters, $response);
    }


    /**
     * Security needs to be enabled to get Object.
     *
     * @return String
     * @throws UsernameNotFoundException
     */
    public function getSecurityUser()
    {
        return $this->securityUtils->getSecurityUser();
    }
}
