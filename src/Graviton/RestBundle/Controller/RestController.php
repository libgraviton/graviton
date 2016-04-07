<?php
/**
 * basic rest controller
 */

namespace Graviton\RestBundle\Controller;

use Graviton\DocumentBundle\Service\FormDataMapperInterface;
use Graviton\ExceptionBundle\Exception\DeserializationException;
use Graviton\ExceptionBundle\Exception\InvalidJsonPatchException;
use Graviton\ExceptionBundle\Exception\MalformedInputException;
use Graviton\ExceptionBundle\Exception\NotFoundException;
use Graviton\ExceptionBundle\Exception\SerializationException;
use Graviton\RestBundle\Validator\Form;
use Graviton\RestBundle\Model\DocumentModel;
use Graviton\RestBundle\Model\PaginatorAwareInterface;
use Graviton\SchemaBundle\SchemaUtils;
use Graviton\DocumentBundle\Form\Type\DocumentType;
use Graviton\RestBundle\Service\RestUtilsInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Knp\Component\Pager\Paginator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\PreconditionRequiredHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Form\FormFactory;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Rs\Json\Patch;
use Rs\Json\Patch\InvalidPatchDocumentJsonException;
use Rs\Json\Patch\InvalidTargetDocumentJsonException;
use Rs\Json\Patch\InvalidOperationException;
use Rs\Json\Patch\FailedTestException;
use Graviton\RestBundle\Service\JsonPatchValidator;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;

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
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var DocumentType
     */
    private $formType;

    /**
     * @var RestUtilsInterface
     */
    private $restUtils;

    /**
     * @var SchemaUtils
     */
    private $schemaUtils;

    /**
     * @var FormDataMapperInterface
     */
    protected $formDataMapper;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var JsonPatchValidator
     */
    private $jsonPatchValidator;

    /**
     * @var Form
     */
    protected $formValidator;

    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    /**
     * @param Response           $response    Response
     * @param RestUtilsInterface $restUtils   Rest utils
     * @param Router             $router      Router
     * @param ValidatorInterface $validator   Validator
     * @param EngineInterface    $templating  Templating
     * @param FormFactory        $formFactory form factory
     * @param DocumentType       $formType    generic form
     * @param ContainerInterface $container   Container
     * @param SchemaUtils        $schemaUtils Schema utils
     */
    public function __construct(
        Response $response,
        RestUtilsInterface $restUtils,
        Router $router,
        ValidatorInterface $validator,
        EngineInterface $templating,
        FormFactory $formFactory,
        DocumentType $formType,
        ContainerInterface $container,
        SchemaUtils $schemaUtils
    ) {
        $this->response = $response;
        $this->restUtils = $restUtils;
        $this->router = $router;
        $this->validator = $validator;
        $this->templating = $templating;
        $this->formFactory = $formFactory;
        $this->formType = $formType;
        $this->container = $container;
        $this->schemaUtils = $schemaUtils;
    }

    /**
     * Setter for the tokenStorage
     *
     * @param TokenStorage $tokenStorage The token storage
     * @return void
     */
    public function setTokenStorage(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Set form data mapper
     *
     * @param FormDataMapperInterface $formDataMapper Form data mapper
     * @return void
     */
    public function setFormDataMapper(FormDataMapperInterface $formDataMapper)
    {
        $this->formDataMapper = $formDataMapper;
    }

    /**
     * @param JsonPatchValidator $jsonPatchValidator Service for validation json patch
     * @return void
     */
    public function setJsonPatchValidator(JsonPatchValidator $jsonPatchValidator)
    {
        $this->jsonPatchValidator = $jsonPatchValidator;
    }

    /**
     * Defines the Form validator to be used.
     *
     * @param Form $validator Validator to be used
     *
     * @return void
     */
    public function setFormValidator(Form $validator)
    {
        $this->formValidator = $validator;
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
        $response = $this->getResponse()
            ->setStatusCode(Response::HTTP_OK);

        $record = $this->findRecord($id);

        return $this->render(
            'GravitonRestBundle:Main:index.json.twig',
            ['response' => $this->serialize($record)],
            $response
        );
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
     * Get a single record from database or throw an exception if it doesn't exist
     *
     * @param mixed $id Record id
     *
     * @throws \Graviton\ExceptionBundle\Exception\NotFoundException
     *
     * @return object $record Document object
     */
    protected function findRecord($id)
    {
        $response = $this->getResponse();

        if (!($record = $this->getModel()->find($id))) {
            $e = new NotFoundException("Entry with id " . $id . " not found!");
            $e->setResponse($response);
            throw $e;
        }

        return $record;
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
     * Serialize the given record and throw an exception if something went wrong
     *
     * @param object|object[] $result Record(s)
     *
     * @throws \Graviton\ExceptionBundle\Exception\SerializationException
     *
     * @return string $content Json content
     */
    protected function serialize($result)
    {
        $response = $this->getResponse();

        try {
            // array is serialized as an object {"0":{...},"1":{...},...} when data contains an empty objects
            // we serialize each item because we can assume this bug affects only root array element
            if (is_array($result) && array_keys($result) === range(0, count($result) - 1)) {
                $result = array_map(
                    function ($item) {
                        return $this->getRestUtils()->serializeContent($item);
                    },
                    $result
                );

                /*
                 * clean up:
                 *
                 * - remove empty entries
                 */
                $result = array_filter($result);

                return '['.implode(',', $result).']';
            }

            return $this->getRestUtils()->serializeContent($result);
        } catch (\Exception $e) {
            $exception = new SerializationException($e);
            $exception->setResponse($response);
            throw $exception;
        }
    }

    /**
     * Get RestUtils service
     *
     * @return \Graviton\RestBundle\Service\RestUtils
     */
    public function getRestUtils()
    {
        return $this->restUtils;
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

        list($app, $module, , $modelName, $schemaType) = explode('.', $request->attributes->get('_route'));

        $schema = $this->schemaUtils->getModelSchema($modelName, $model);

        // Security is optional configured in Parameters
        try {
            /** @var SecurityUser $securityUser */
            $securityUser = $this->getSecurityUser();
        } catch (PreconditionRequiredHttpException $e) {
            $securityUser = null;
        }

        if ($model instanceof PaginatorAwareInterface && !$model->hasPaginator()) {
            $paginator = new Paginator();
            $model->setPaginator($paginator);
        }

        $response = $this->getResponse()
            ->setStatusCode(Response::HTTP_OK);

        return $this->render(
            'GravitonRestBundle:Main:index.json.twig',
            ['response' => $this->serialize($model->findAll($request, $securityUser, $schema))],
            $response
        );
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

        $this->formValidator->checkJsonRequest($request, $response);
        $record = $this->formValidator->checkForm(
            $this->formValidator->getForm($request, $model),
            $model,
            $this->formDataMapper,
            $request->getContent()
        );

        // Insert the new record
        $record = $this->getModel()->insertRecord($record);

        // store id of new record so we dont need to reparse body later when needed
        $request->attributes->set('id', $record->getId());

        // Set status code
        $response->setStatusCode(Response::HTTP_CREATED);

        $response->headers->set(
            'Location',
            $this->getRouter()->generate($this->getRouteName($request), array('id' => $record->getId()))
        );

        return $response;
    }

    /**
     * Deserialize the given content throw an exception if something went wrong
     *
     * @param string $content       Request content
     * @param string $documentClass Document class
     *
     * @throws DeserializationException
     *
     * @return object $record Document
     */
    protected function deserialize($content, $documentClass)
    {
        $response = $this->getResponse();

        try {
            $record = $this->getRestUtils()->deserializeContent(
                $content,
                $documentClass
            );
        } catch (\Exception $e) {
            // pass the previous exception in this case to get the error message in the handler
            // http://php.net/manual/de/exception.getprevious.php
            $exception = new DeserializationException("Deserialization failed", $e);

            // at the moment, the response has to be set on the exception object.
            // try to refactor this and return the graviton.rest.response if none is set...
            $exception->setResponse($response);
            throw $exception;
        }

        return $record;
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

        $this->formValidator->checkJsonRequest($request, $response);

        $record = $this->formValidator->checkForm(
            $this->formValidator->getForm($request, $model),
            $model,
            $this->formDataMapper,
            $request->getContent()
        );

        // does it really exist??
        $upsert = false;
        try {
            $this->findRecord($id);
        } catch (NotFoundException $e) {
            // who cares, we'll upsert it
            $upsert = true;
        }

        // handle missing 'id' field in input to a PUT operation
        // if it is settable on the document, let's set it and move on.. if not, inform the user..
        if ($record->getId() != $id) {
            // try to set it..
            if (is_callable(array($record, 'setId'))) {
                $record->setId($id);
            } else {
                throw new MalformedInputException('No ID was supplied in the request payload.');
            }
        }

        // And update the record, if everything is ok
        if ($upsert) {
            $this->getModel()->insertRecord($record);
        } else {
            $this->getModel()->updateRecord($id, $record);
        }

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
        $this->formValidator->checkJsonRequest($request, $response);

        // Check JSON Patch request
        $this->formValidator->checkJsonPatchRequest(json_decode($request->getContent(), 1));

        // Find record && apply $ref converter
        $record = $this->findRecord($id);
        $jsonDocument = $this->serialize($record);

        // Check/validate JSON Patch
        if (!$this->jsonPatchValidator->validate($jsonDocument, $request->getContent())) {
            throw new InvalidJsonPatchException($this->jsonPatchValidator->getException()->getMessage());
        }

        try {
            // Apply JSON patches
            $patch = new Patch($jsonDocument, $request->getContent());
            $patchedDocument = $patch->apply();
        } catch (InvalidPatchDocumentJsonException $e) {
            throw new InvalidJsonPatchException($e->getMessage());
        } catch (InvalidTargetDocumentJsonException $e) {
            throw new InvalidJsonPatchException($e->getMessage());
        } catch (InvalidOperationException $e) {
            throw new InvalidJsonPatchException($e->getMessage());
        } catch (FailedTestException $e) {
            throw new InvalidJsonPatchException($e->getMessage());
        }

        // Validate result object
        $model = $this->getModel();
        $record = $this->formValidator->checkForm(
            $this->formValidator->getForm($request, $model),
            $model,
            $this->formDataMapper,
            $patchedDocument
        );

        // Update object
        $this->getModel()->updateRecord($id, $record);

        // Set status code
        $response->setStatusCode(Response::HTTP_OK);

        // Set Content-Location header
        $response->headers->set(
            'Content-Location',
            $this->getRouter()->generate($this->getRouteName($request), array('id' => $record->getId()))
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

        // does this record exist?
        $this->findRecord($id);

        $this->getModel()->deleteRecord($id);
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
        $response->setStatusCode(Response::HTTP_OK);

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
            ['response' => $this->serialize($schema)],
            $response
        );
    }

    /**
     * Get the validator
     *
     * @return ValidatorInterface
     */
    public function getValidator()
    {
        return $this->validator;
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
     * @param Request $request request
     * @return string
     */
    private function getRouteName(Request $request)
    {
        $routeName = $request->get('_route');
        $routeParts = explode('.', $routeName);
        $routeType = end($routeParts);

        if ($routeType == 'post') {
            $routeName = substr($routeName, 0, -4) . 'get';
        }

        return $routeName;
    }

    /**
     * Security needs to be enabled to get Object.
     *
     * @return SecurityUser
     * @throws PreconditionRequiredHttpException
     */
    public function getSecurityUser()
    {
        /** @var PreAuthenticatedToken $token */
        if (($token = $this->tokenStorage->getToken())
            && ($user = $token->getUser()) instanceof UserInterface ) {
            return $user;
        }

        throw new PreconditionRequiredHttpException('Not allowed');
    }
}
