<?php
/**
 * basic rest controller
 */

namespace Graviton\RestBundle\Controller;

use Graviton\ExceptionBundle\Exception\InvalidJsonPatchException;
use Graviton\ExceptionBundle\Exception\MalformedInputException;
use Graviton\ExceptionBundle\Exception\SerializationException;
use Graviton\RestBundle\Model\DocumentModel;
use Graviton\RestBundle\Service\RestUtils;
use Graviton\SecurityBundle\Service\SecurityUtils;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Rs\Json\Patch;
use Graviton\RestBundle\Service\JsonPatchValidator;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * This is a basic rest controller. It should fit the most needs but if you need to add some
 * extra functionality you can extend it and overwrite single/all actions.
 * You can also extend the model class to add some extra logic before save
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RestController
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DocumentModel
     */
    private DocumentModel $model;

    /**
     * @var RestUtils
     */
    protected RestUtils $restUtils;

    /**
     * @var Router
     */
    private Router $router;

    /**
     * @var JsonPatchValidator
     */
    private JsonPatchValidator $jsonPatchValidator;

    /**
     * @var SecurityUtils
     */
    protected SecurityUtils $securityUtils;

    /**
     * @param RestUtils          $restUtils Rest Utils
     * @param Router             $router    Router
     * @param JsonPatchValidator $jsonPatch Service for validation json patch
     * @param SecurityUtils      $security  The securityUtils service
     */
    public function __construct(
        RestUtils          $restUtils,
        Router             $router,
        JsonPatchValidator $jsonPatch,
        SecurityUtils      $security
    ) {
        $this->restUtils = $restUtils;
        $this->router = $router;
        $this->jsonPatchValidator = $jsonPatch;
        $this->securityUtils = $security;
    }

    /**
     * get Logger
     *
     * @return LoggerInterface Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * set Logger
     *
     * @param LoggerInterface $logger logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Function for classes that inherit from this controller, to give them the opportunity to deserialize stuff
     *
     * @param string $content       content
     * @param string $documentClass class
     *
     * @return object deserialized object
     */
    protected function deserialize($content, $documentClass)
    {
        return $this->restUtils->deserialize($content, $documentClass);
    }

    /**
     * Function for classes that inherit from this controller, to give them the opportunity to serialize stuff
     *
     * @param object $object object
     *
     * @return string json
     */
    protected function serialize($object)
    {
        return $this->restUtils->serialize($object);
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
        $this->logger->info('REST: getAction');

        $document = $this->getModel()->getSerialised($id, $request);

        $this->addRequestAttributes($request);

        return new JsonResponse($document, Response::HTTP_OK, [], true);
    }

    /**
     * Get the response object
     *
     * @return \Symfony\Component\HttpFoundation\Response $response Response object
     */
    public function getResponse()
    {
        trigger_deprecation(
            'graviton',
            '8.0.0',
            'getResponse() on RestController will be removed in 9.0'
        );
        return new Response();
    }

    /**
     * Return the model
     *
     * @return DocumentModel $model Model
     *
     * @throws \Exception in case no model was defined.
     *
     */
    public function getModel() : DocumentModel
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
    public function setModel(DocumentModel $model) : void
    {
        $this->model = $model;
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

        $this->logger->info('REST: allAction -> got model, starting findAll() on QueryService');
        $data = $model->findAll($request);

        $this->addRequestAttributes($request);

        $response = new StreamedResponse();
        $response->headers->set('x-accel-buffering', 'no');
        $response->headers->set('content-type', 'application/json; charset=UTF-8');
        $response->setCallback(
            function () use ($data): void {
                $isFirst = true;

                $this->logger->info('REST: allAction -> got data, starting to serialize() inside callback');

                echo "[";

                foreach ($data as $record) {
                    // all except first record need a "," to separate
                    if (!$isFirst) {
                        echo ",";
                    } else {
                        $isFirst = false;
                    }

                    try {
                        echo $this->restUtils->serializeContent($record);
                        flush();
                    } catch (\Exception $e) {
                        // skipping row! error was logged to STDOUT of service
                    }
                }

                echo "]";
                flush();

                $this->logger->info('REST: allAction -> finished serializing content.');
            }
        );

        return $response;
    }

    /**
     * Writes a new Entry to the database
     *
     * @param Request $request Current http request
     *
     * @return \Symfony\Component\HttpFoundation\Response $response Result of action with data (if successful)
     * @throws \Exception
     */
    public function postAction(Request $request)
    {
        $this->logger->info('REST: postAction');

        // Get the response object from container
        $model = $this->getModel();

        // will throw if not ok
        $this->restUtils->validateRequest($request, $model);

        $record = $this->restUtils->getEntityFromRequest($request, $model);

        // Insert the new record
        $record = $model->insertRecord($record);

        // store id of new record so we dont need to reparse body later when needed
        $request->attributes->set('id', $record->getId());
        $this->addRequestAttributes($request);

        return new JsonResponse(
            '',
            Response::HTTP_CREATED,
            [],
            true
        );
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
     * @return Response $response Result of action with data (if successful)
     * @throws MalformedInputException
     *
     */
    public function putAction($id, Request $request)
    {
        $this->logger->info('REST: putAction');

        $model = $this->getModel();

        // will throw if not ok
        $this->restUtils->validateRequest($request, $model);

        $record = $this->restUtils->getEntityFromRequest($request, $model);

        // ID collision between payload and ID in path or empty id!
        if ($record->getId() != $id) {
            if (empty($record->getId()) && is_callable(array($record, 'setId'))) {
                $record->setId($id);
            } else {
                throw new MalformedInputException('Record ID in your payload must be the same');
            }
        }

        // And update the record, if everything is ok
        if (!$this->getModel()->recordExists($id)) {
            $this->getModel()->insertRecord($record);
        } else {
            $this->getModel()->updateRecord($id, $record);
        }

        $this->addRequestAttributes($request);
        $request->attributes->set('id', $record->getId());

        return new JsonResponse('', Response::HTTP_NO_CONTENT, [], true);
    }

    /**
     * Patch a record
     *
     * @param Number  $id      ID of record
     * @param Request $request Current http request
     *
     * @return Response $response Result of action with data (if successful)
     * @throws MalformedInputException
     *
     */
    public function patchAction($id, Request $request)
    {
        $this->logger->info('REST: patchAction');

        $model = $this->getModel();

        // first, validate the PATCH request itself!
        $this->restUtils->validateRequest($request, $model);

        // Check JSON Patch request
        $this->restUtils->checkJsonPatchRequest(json_decode($request->getContent(), 1));

        // Find record && apply $ref converter
        $jsonDocument = $model->getSerialised($id, null);

        try {
            // Check if valid
            $this->jsonPatchValidator->validate($jsonDocument, $request->getContent());
            // Apply JSON patches
            $patch = new Patch($jsonDocument, $request->getContent());
            $patchedDocument = $patch->apply();
        } catch (\Exception $e) {
            throw new InvalidJsonPatchException(prev: $e);
        }

        // if document hasn't changed, pass HTTP_NOT_MODIFIED and exit
        if ($jsonDocument == $patchedDocument) {
            return new JsonResponse('', Response::HTTP_NOT_MODIFIED, [], true);
        }

        // now we have the 'destination object' -> validate this again as it would be a PUT request!
        $putRequest = new Request(
            $request->query->all(),
            $request->request->all(),
            $request->attributes->all(),
            $request->cookies->all(),
            $request->files->all(),
            array_merge(
                $request->server->all(),
                [
                    'REQUEST_METHOD' => 'PUT'
                ]
            ),
            $patchedDocument
        );

        $putRequest->headers->replace($request->headers->all());

        // Validate result object
        $this->restUtils->validateRequest($putRequest, $model);

        $record = $this->restUtils->getEntityFromRequest($putRequest, $model);

        // Update object
        $this->getModel()->updateRecord($id, $record);

        $this->addRequestAttributes($request);

        return new JsonResponse(
            '',
            Response::HTTP_OK,
            [
                'Content-Location' => $this->getRouter()->generate($request->get('_route'), ['id' => $record->getId()])
            ],
            true
        );
    }

    /**
     * Deletes a record
     *
     * @param Number  $id      ID of record
     * @param Request $request request
     *
     * @return Response $response Result of the action
     */
    public function deleteAction($id, Request $request)
    {
        $this->logger->info('REST: deleteAction');

        $this->model->deleteRecord($id);
        $this->addRequestAttributes($request);

        return new JsonResponse('', Response::HTTP_NO_CONTENT, [], true);
    }

    /**
     * Return OPTIONS results.
     *
     * @param Request $request Current http request
     *
     * @return \Symfony\Component\HttpFoundation\Response $response Result of the action
     * @throws SerializationException
     */
    public function optionsAction(Request $request)
    {
        return new JsonResponse('', Response::HTTP_NO_CONTENT, [], true);
    }

    /**
     * should return the current model schema
     *
     * @param Request $request request
     *
     * @return array the schema encoded
     */
    public function getModelSchema(Request $request) : array
    {
        $schemaFile = $this->getModel()->getSchemaPath();

        if (!file_exists($schemaFile)) {
            throw new \LogicException('The schemaFile does not exist!');
        }

        return \json_decode(file_get_contents($schemaFile), true);
    }

    /**
     * Return schema GET results.
     *
     * @param Request $request Current http request
     *
     * @return Response $response Result of the action
     * @throws SerializationException
     */
    public function schemaAction(Request $request)
    {
        $modelSchema = $this->getModelSchema($request);

        $format = 'json';
        if (str_ends_with($request->getPathInfo(), '.yaml')) {
            $format = 'yaml';
        }

        if ($format == 'json') {
            return new JsonResponse($modelSchema, 200, []);
        }

        return new Response(
            Yaml::dump($modelSchema, 30, 2),
            200,
            ['content-type' => 'application/yaml']
        );
    }

    /**
     * Security needs to be enabled to get Object.
     *
     * @return ?UserInterface user
     */
    public function getSecurityUser(): ?UserInterface
    {
        return $this->securityUtils->getSecurityUser();
    }

    /**
     * add some attributes to request
     *
     * @param Request $request request
     *
     * @return void
     */
    private function addRequestAttributes(Request $request)
    {
        $request->attributes->set('varnishTags', $this->getModel()->getEntityClass(true));
    }
}
