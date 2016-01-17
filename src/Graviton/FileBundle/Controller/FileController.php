<?php
/**
 * controller for gaufrette based file store
 */

namespace Graviton\FileBundle\Controller;

use Graviton\FileBundle\FileManager;
use Graviton\RestBundle\Controller\RestController;
use Graviton\RestBundle\Service\RestUtilsInterface;
use Graviton\SchemaBundle\SchemaUtils;
use GravitonDyn\FileBundle\Document\File;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactory;
use Graviton\DocumentBundle\Form\Type\DocumentType;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class FileController extends RestController
{
    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * @param Response           $response    Response
     * @param RestUtilsInterface $restUtils   Rest utils
     * @param Router             $router      Router
     * @param ValidatorInterface $validator   Validator
     * @param EngineInterface    $templating  Templating
     * @param FormFactory        $formFactory form factory
     * @param DocumentType       $formType    generic form
     * @param ContainerInterface $container   Container
     * @param SchemaUtils        $schemaUtils schema utils
     * @param FileManager        $fileManager Handles file specific tasks
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
        SchemaUtils $schemaUtils,
        FileManager $fileManager
    ) {
        parent::__construct(
            $response,
            $restUtils,
            $router,
            $validator,
            $templating,
            $formFactory,
            $formType,
            $container,
            $schemaUtils
        );
        $this->fileManager = $fileManager;
    }

    /**
     * Writes a new Entry to the database
     *
     * @param Request $request Current http request
     *
     * @return Response $response Result of action with data (if successful)
     */
    public function postAction(Request $request)
    {
        $response = $this->getResponse();
        $fileData = $this->validateRequest($request, $response, $request->get('metadata'));
        $files = $this->fileManager->saveFiles($request, $this->getModel(), $fileData);

        // store id of new record so we don't need to re-parse body later when needed
        $request->attributes->set('id', $files[0]);

        // Set status code and content
        $response->setStatusCode(Response::HTTP_CREATED);

        // TODO: this not is correct for multiple uploaded files!!
        // TODO: Probably use "Link" header to address this.
        $locations = $this->determineRoutes($request->get('_route'), $files, ['post', 'postNoSlash']);
        $response->headers->set(
            'Location',
            $locations[0]
        );

        return $response;
    }

    /**
     * respond with document if non json mime-type is requested
     *
     * @param Request $request Current http request
     * @param string  $id      id of file
     *
     * @return Response
     */
    public function getAction(Request $request, $id)
    {
        $accept = $request->headers->get('accept');
        if (substr(strtolower($accept), 0, 16) === 'application/json') {
            return parent::getAction($request, $id);
        }
        $response = $this->getResponse();

        if (!$this->fileManager->has($id)) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);

            return $response;
        }

        $record = $this->findRecord($id);
        $data = $this->fileManager->read($id);

        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', $record->getMetadata()->getMime());

        return $this->render(
            'GravitonFileBundle:File:index.raw.twig',
            ['data' => $data],
            $response
        );
    }

    /**
     * Update a record
     *
     * @param Number  $id      ID of record
     * @param Request $request Current http request
     *
     * @return Response $response Result of action with data (if successful)
     */
    public function putAction($id, Request $request)
    {
        $contentType = $request->headers->get('Content-Type');
        if (substr(strtolower($contentType), 0, 16) === 'application/json') {
            return parent::putAction($id, $request);
        }
        if (0 === strpos($contentType, 'multipart/form-data')) {
            $request = $this->normalizeRequest($request);
        }

        $response = $this->getResponse();
        $fileData = $this->validateRequest($request, $response, $request->get('metadata'));
        $files = $this->fileManager->saveFiles($request, $this->getModel(), $fileData);

        // store id of new record so we don't need to re-parse body later when needed
        $request->attributes->set('id', $files[0]);

        $response->setStatusCode(Response::HTTP_NO_CONTENT);

        // no service sends Location headers on PUT - /file shouldn't as well

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
        if ($this->fileManager->has($id)) {
            $this->fileManager->delete($id);
        }

        return parent::deleteAction($id);
    }

    /**
     * Determines the routes and replaces the http method
     *
     * @param string $routeName  Name of the route to be generated
     * @param array  $files      Set of uploaded files
     * @param array  $routeTypes Set of route types to be recognized
     *
     * @return array
     */
    private function determineRoutes($routeName, array $files, array $routeTypes)
    {
        $locations = [];
        $newRouteName = '';
        foreach ($routeTypes as $routeType) {
            $routeParts = explode('.', $routeName);

            if ($routeType == array_pop($routeParts)) {
                $reduce = (-1) * strlen($routeType);
                $newRouteName = substr($routeName, 0, $reduce).'get';
                break;
            }
        }

        if (!empty($newRouteName)) {
            foreach ($files as $id) {
                $locations[] = $this->getRouter()->generate($newRouteName, array('id' => $id));
            }
        }

        return $locations;
    }

    /**
     * Validates the provided request
     *
     * @param Request  $request  Http request to be validated
     * @param Response $response Http response to be returned in case of an error
     * @param string   $fileData Alternative content to be validated
     *
     * @throws \Exception
     * @return File|null
     */
    private function validateRequest(Request $request, Response $response, $fileData = '')
    {
        if (!empty($fileData)) {
            $this->formValidator->checkJsonRequest($request, $response, $fileData);
            $model = $this->getModel();
            return $this->formValidator->checkForm(
                $this->formValidator->getForm($request, $model),
                $model,
                $this->formDataMapper,
                $fileData
            );
        }
    }

    /**
     * Gathers information into a request
     *
     * @param Request $request master request sent by client.
     *
     * @return Request
     */
    private function normalizeRequest(Request $request)
    {
        $contentData = $this->fileManager->extractDataFromRequestContent($request);
        $normalized = $request->duplicate(
            null,
            null,
            $contentData['attributes'],
            null,
            $contentData['files']
        );

        return $normalized;
    }
}
