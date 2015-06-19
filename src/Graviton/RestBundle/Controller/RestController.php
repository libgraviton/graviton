<?php
/**
 * basic rest controller
 */

namespace Graviton\RestBundle\Controller;

use Graviton\ExceptionBundle\Exception\DeserializationException;
use Graviton\ExceptionBundle\Exception\MalformedInputException;
use Graviton\ExceptionBundle\Exception\NotFoundException;
use Graviton\ExceptionBundle\Exception\SerializationException;
use Graviton\ExceptionBundle\Exception\ValidationException;
use Graviton\ExceptionBundle\Exception\NoInputException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Graviton\I18nBundle\Document\TranslatableDocumentInterface;
use Graviton\RestBundle\Model\ModelInterface;
use Graviton\RestBundle\Model\PaginatorAwareInterface;
use Graviton\SchemaBundle\SchemaUtils;
use Graviton\DocumentBundle\Form\Type\DocumentType;
use Graviton\RestBundle\Service\RestUtilsInterface;
use Graviton\I18nBundle\Repository\LanguageRepository;
use Knp\Component\Pager\Paginator;
use Rs\Json\Patch;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Form\FormFactory;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

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
     * @var ModelInterface
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
     * @var Router
     */
    private $router;
    
    /**
     * @var LanguageRepository
     */
    private $language;
    
    /**
     * @var ValidatorInterface
     */
    private $validator;
    
    /**
     * @var EngineInterface
     */
    private $templating;
    
    /**
     * @param Response           $response    Response
     * @param RestUtilsInterface $restUtils   Rest utils
     * @param Router             $router      Router
     * @param LanguageRepository $language    Language
     * @param ValidatorInterface $validator   Validator
     * @param EngineInterface    $templating  Templating
     * @param FormFactory        $formFactory form factory
     * @param DocumentType       $formType    generic form
     * @param ContainerInterface $container   Container
     */
    public function __construct(
        Response $response,
        RestUtilsInterface $restUtils,
        Router $router,
        LanguageRepository $language,
        ValidatorInterface $validator,
        EngineInterface $templating,
        FormFactory $formFactory,
        DocumentType $formType,
        ContainerInterface $container
    ) {
        $this->response = $response;
        $this->restUtils = $restUtils;
        $this->router = $router;
        $this->language = $language;
        $this->validator = $validator;
        $this->templating = $templating;
        $this->formFactory = $formFactory;
        $this->formType = $formType;
        $this->container = $container;
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
     * @return ModelInterface $model Model
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
     * Serialize the given record and throw an exception if something went wrong
     *
     * @param object $result Record
     *
     * @throws \Graviton\ExceptionBundle\Exception\SerializationException
     *
     * @return string $content Json content
     */
    protected function serialize($result)
    {
        $response = $this->getResponse();

        try {
            $content = $this->getRestUtils()->serializeContent($result);
        } catch (\Exception $e) {
            $exception = new SerializationException($e);
            $exception->setResponse($response);
            throw $exception;
        }

        return $content;
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

        if ($model instanceof PaginatorAwareInterface && !$model->hasPaginator()) {
            $paginator = new Paginator();
            $model->setPaginator($paginator);
        }

        $response = $this->getResponse()
            ->setStatusCode(Response::HTTP_OK);

        return $this->render(
            'GravitonRestBundle:Main:index.json.twig',
            ['response' => $this->serialize($model->findAll($request))],
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

        $this->checkJsonRequest($request, $response);

        $form = $this->getForm($request);
        $form->handleRequest($request);
        $form->submit(json_decode(str_replace('"$ref"', '"ref"', $request->getContent()), true), false);
        if (!$form->isValid()) {
            throw new ValidationException("Validation failed", $form->getErrors(true));
        } else {
            $record = $form->getData();
        }

        // Insert the new record
        $record = $this->getModel()->insertRecord($record);

        // store id of new record so we dont need to reparse body later when needed
        $request->attributes->set('id', $record->getId());

        // Set status code
        $response->setStatusCode(Response::HTTP_CREATED);

        $routeName = $request->get('_route');
        $routeParts = explode('.', $routeName);
        $routeType = end($routeParts);

        if ($routeType == 'post') {
            $routeName = substr($routeName, 0, -4) . 'get';
        }

        $response->headers->set(
            'Location',
            $this->getRouter()->generate($routeName, array('id' => $record->getId()))
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

        $this->checkJsonRequest($request, $response);

        $form->getForm($request);
        $form->handleRequest($request);
        $form->submit(json_decode(str_replace('"$ref"', '"ref"', $request->getContent()), true), false);
        if (!$form->isValid()) {
            throw new ValidationException('Validation failed', $form->getErrors(true));
        } else {
            $record = $form->getData();
        }

        // does it really exist??
        $this->findRecord($id);

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
        $this->getModel()->updateRecord($id, $record);
        $response->setStatusCode(Response::HTTP_OK);

        return $this->render(
            'GravitonRestBundle:Main:index.json.twig',
            ['response' => $this->serialize($record)],
            $response
        );
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
        $response->setStatusCode(Response::HTTP_OK);

        return $this->render(
            'GravitonRestBundle:Main:index.json.twig',
            array('response' => $response->getContent()),
            $response
        );
    }

    /**
     * Patch a record (partial update) -> DO NOT USE THIS (or refactor it...)
     * We tried to implement the jsonpatch rfc but this is not possible
     * because of doctrine odm / serializer
     *
     * @param Number  $id      ID of record
     *
     * @param Request $request Current http request
     *
     * @throws DeserializationException
     * @throws NotFoundException
     * @throws Patch\FailedTestException
     * @throws SerializationException
     * @throws \Exception
     * @return \Symfony\Component\HttpFoundation\Response $response Result of the action
     */
    public function patchAction($id, Request $request)
    {
        $response = $this->getResponse()
            ->setStatusCode(Response::HTTP_NOT_FOUND);

        $record = $this->findRecord($id);

        // Get the patch params from request
        $requestContent = $request->getContent();

        if (!is_null($record) && !empty($requestContent)) {
            // get the record as json to handle json-patch
            $jsonString = $this->serialize($record);

            // Now replace existing values with the new ones
            $patch = new Patch($jsonString, $requestContent);

            $newRecord = $this->deserialize(
                $patch->apply(),
                $this->getModel()->getEntityClass()
            );

            // disabled here, see comment in postAction()..
            //$this->validateRecord($newRecord);

            $this->getModel()->updateRecord($id, $newRecord);
            //$response = $this->container->get('graviton.rest.response.204');
            $response->setStatusCode(Response::HTTP_NO_CONTENT);
        }

        return $this->render(
            'GravitonRestBundle:Main:index.json.twig',
            array('response' => $response->getContent()),
            $response
        );
    }

    /**
     * Return OPTIONS results.
     *
     * @param Request $request Current http request
     * @param string  $id      ID of record
     *
     * @throws SerializationException
     * @return \Symfony\Component\HttpFoundation\Response $response Result of the action
     */
    public function optionsAction(Request $request, $id = null)
    {
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
            $this->language->findAll()
        );

        $response = $this->response;
        $response->setStatusCode(Response::HTTP_OK);

        $schemaMethod = 'getModelSchema';
        if (!$id && $schemaType != 'canonicalIdSchema') {
            $schemaMethod = 'getCollectionSchema';
        }
        $schema = SchemaUtils::$schemaMethod($modelName, $model, $translatableFields, $languages);

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
     * validate raw json input
     *
     * @param Request  $request  request
     * @param Response $response response
     *
     * @return boolean
     */
    private function checkJsonRequest(Request $request, Response $response)
    {
        $content = $request->getContent();

        if (is_resource($content)) {
            throw new BadRequestHttpException('unexpected resource in validation');
        }

        // Decode the json from request
        if (!($input = json_decode($content, true)) && JSON_ERROR_NONE === json_last_error()) {
            $e = new NoInputException();
            $e->setResponse($response);
            throw $e;
        }

        // specially check for parse error ($input decodes to null) and report accordingly..
        if (is_null($input) && JSON_ERROR_NONE !== json_last_error()) {
            $e = new MalformedInputException($this->getLastJsonErrorMessage());
            $e->setErrorType(json_last_error());
            $e->setResponse($response);
            //$e->setResponse($event->getResponse());
            throw $e;
        }

        if ($request->getMethod() == 'PUT' && array_key_exists('id', $input)) {
            // we need to check for id mismatches....
            if ($request->attributes->get('id') != $input['id']) {
                throw new BadRequestHttpException('Record ID in your payload must be the same');
            }
        }
    }
    /**
     * Used for backwards compatibility to PHP 5.4
     *
     * @return string
     */
    private function getLastJsonErrorMessage()
    {
        $message = 'Unable to decode JSON string';

        if (function_exists('json_last_error_msg')) {
            $message = json_last_error_msg();
        }

        return $message;
    }

    /**
     * @param Request $request request
     * @return DynamicForm
     */
    private function getForm(Request $request)
    {
        list($service) = explode(':', $request->attributes->get('_controller'));
        $this->formType->initialize($service);
        return $this->formFactory->create($this->formType);
    }
}
