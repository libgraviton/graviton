<?php
/**
 * basic rest controller
 */

namespace Graviton\RestBundle\Controller;

use Graviton\ExceptionBundle\Exception\InvalidJsonPatchException;
use Graviton\ExceptionBundle\Exception\SerializationException;
use Graviton\RestBundle\Model\DocumentModel;
use Graviton\RestBundle\Service\JsonPatchValidator;
use Graviton\RestBundle\Service\RestUtils;
use Graviton\RestBundle\Trait\SchemaTrait;
use Graviton\SecurityBundle\Service\SecurityUtils;
use Psr\Log\LoggerInterface;
use Rs\Json\Patch;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\User\UserInterface;

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

    use SchemaTrait;

    /**
     * @var ?LoggerInterface
     */
    private ?LoggerInterface $logger;

    /**
     * @var DocumentModel
     */
    private DocumentModel $model;

    /**
     * @param RestUtils          $restUtils Rest Utils
     * @param Router             $router    Router
     * @param JsonPatchValidator $jsonPatchValidator Service for validation json patch
     * @param SecurityUtils      $securityUtils  The securityUtils service
     */
    public function __construct(
        protected readonly RestUtils $restUtils,
        protected readonly Router $router,
        protected readonly JsonPatchValidator $jsonPatchValidator,
        protected readonly SecurityUtils $securityUtils
    ) {
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
        $this->getModel()->addRequestAttributes($id, $request);

        return new JsonResponse($document, Response::HTTP_OK, [], true);
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

        $this->getModel()->addRequestAttributes(null, $request);

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
                        // skip also comma once again!
                        $isFirst = true;
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

        $response = new JsonResponse(
            '',
            Response::HTTP_CREATED,
            [],
            true
        );

        // will throw if not ok
        $psrRequest = $this->restUtils->validateRequest($request, $response, $model);
        $record = $this->restUtils->getEntityFromRequest($psrRequest, $model);

        // Insert the new record
        $model->insertRecord($record, $request);

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
     * @return Response $response Result of action with data (if successful)
     *
     */
    public function putAction($id, Request $request)
    {
        $this->logger->info('REST: putAction');

        $model = $this->getModel();

        $response = new JsonResponse('', Response::HTTP_NO_CONTENT, [], true);

        // will throw if not ok
        $psrRequest = $this->restUtils->validateRequest($request, $response, $model);
        $record = $this->restUtils->getEntityFromRequest($psrRequest, $model);

        $this->getModel()->upsertRecord($id, $record, $request);

        return $response;
    }

    /**
     * Patch a record
     *
     * @param Number  $id      ID of record
     * @param Request $request Current http request
     *
     * @return Response $response Result of action with data (if successful)
     *
     */
    public function patchAction($id, Request $request)
    {
        $this->logger->info('REST: patchAction');

        $model = $this->getModel();

        // first, validate the PATCH request itself! skip body checks here.
        $this->restUtils->validateRequest($request, new Response(), $model, true);

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

        $response = new JsonResponse(
            '',
            Response::HTTP_OK,
            [
                'Content-Location' => $this->getRouter()->generate($request->get('_route'), ['id' => $id])
            ],
            true
        );

        // Validate result object
        $putRequest = $this->restUtils->validateRequest($putRequest, $response, $model);
        $record = $this->restUtils->getEntityFromRequest($putRequest, $model);

        // Update object
        $this->getModel()->updateRecord($id, $record, $request);

        return $response;
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

        $response = new JsonResponse('', Response::HTTP_NO_CONTENT, [], true);
        $this->restUtils->validateRequest($request, $response, $this->getModel());

        $this->model->deleteRecord($id, $request);

        return $response;
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
        return $this->getResponseFromSchema(
            $this->getModelSchema($request),
            str_ends_with($request->getPathInfo(), '.json') ? 'json' : 'yaml'
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
}
