<?php
/**
 * controller for gaufrette based file store
 */

namespace Graviton\FileBundle\Controller;

use Graviton\FileBundle\FileManager;
use Graviton\RestBundle\Controller\RestController;
use Graviton\RestBundle\Service\RestUtilsInterface;
use Graviton\SchemaBundle\SchemaUtils;
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
        $files = $this->fileManager->saveFiles($request, $this->getModel());

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

        $file = $this->fileManager->saveFile($id, $request->getContent());

        $record = $this->findRecord($id);
        $record->getMetadata()
            ->setSize((int) $file->getSize())
            ->setMime($contentType)
            ->setModificationdate(new \DateTime());

        $this->getModel()->updateRecord($id, $record);

        // store id of new record so we don't need to re-parse body later when needed
        $request->attributes->set('id', $record->getId());

        $response = $this->getResponse();
        $response->setStatusCode(Response::HTTP_NO_CONTENT);

        // TODO: this not is correct for multiple uploaded files!!
        // TODO: Probably use "Link" header to address this.
        $locations = $this->determineRoutes($request->get('_route'), [$file], ['put', 'putNoSlash']);
        $response->headers->set(
            'Location',
            $locations[0]
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
        foreach($routeTypes as $routeType) {
            $reduce = (-1) * strlen($routeType);
            $routeName = substr($routeName, 0, $reduce).'get';
        }

        $locations = [];
        foreach ($files as $id) {
            $locations[] = $this->getRouter()->generate($routeName, array('id' => $id));
        }

        return $locations;
    }
}
