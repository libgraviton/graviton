<?php
/**
 * controller for gaufrette based file store
 */

namespace Graviton\FileBundle\Controller;

use Graviton\RestBundle\Controller\RestController;
use Graviton\RestBundle\Service\RestUtilsInterface;
use Graviton\I18nBundle\Repository\LanguageRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactory;
use Graviton\DocumentBundle\Form\Type\DocumentType;
use Gaufrette\FileSystem;
use Gaufrette\File;
use GravitonDyn\FileBundle\Document\FileMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class FileController extends RestController
{
    /**
     * @var FileSystem
     */
    private $gaufrette;

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
     * @param FileSystem         $gaufrette   file system abstraction layer for s3 and more
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
        ContainerInterface $container,
        Filesystem $gaufrette
    ) {
        parent::__construct(
            $response,
            $restUtils,
            $router,
            $language,
            $validator,
            $templating,
            $formFactory,
            $formType,
            $container
        );
        $this->gaufrette = $gaufrette;
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
        $response = $this->getResponse();

        $entityClass = $this->getModel()->getEntityClass();
        $record = new $entityClass;

        // Insert the new record
        $record = $this->getModel()->insertRecord($record);

        // store id of new record so we dont need to reparse body later when needed
        $request->attributes->set('id', $record->getId());

        $file = $this->saveFile($record->getId(), $request->getContent());
        // update record with file metadata
        $meta = new FileMetadata();
        $meta->setSize((int) $file->getSize())
            ->setMime($request->headers->get('Content-Type'));
        $record->setMetadata($meta);
        $record = $this->getModel()->updateRecord($record->getId(), $record);

        // Set status code and content
        $response->setStatusCode(Response::HTTP_CREATED);
        $response->setContent($this->serialize($record));

        $routeName = $request->get('_route');
        $routeParts = explode('.', $routeName);
        $routeType = end($routeParts);

        if ($routeType == 'post') {
            $routeName = substr($routeName, 0, -4).'get';
        }

        $response->headers->set(
            'Location',
            $this->getRouter()->generate($routeName, array('id' => $record->getId()))
        );

        return $this->render(
            'GravitonRestBundle:Main:index.json.twig',
            array('response' => $response->getContent()),
            $response
        );
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

        if (!$this->gaufrette->has($id)) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);

            return $response;
        }

        $record = $this->findRecord($id);
        $data = $this->gaufrette->read($id);

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

        $record = $this->findRecord($id);

        $file = $this->saveFile($id, $request->getContent());

        $record->getMetadata()
            ->setSize((int) $file->getSize())
            ->setMime($contentType);

        $this->getModel()->updateRecord($id, $record);

        return parent::getAction($request, $id);

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
        if ($this->gaufrette->has($id)) {
            $this->gaufrette->delete($id);
        }

        return parent::deleteAction($id);
    }

    /**
     * Save or update a file
     *
     * @param $id   Number ID of file
     * @param $data String content to save
     *
     * @return Gaufrette\File $file the saved file
     *
     * @throws BadRequestHttpException
     */
    private function saveFile($id, $data)
    {
        if (is_resource($data)) {
            throw new BadRequestHttpException('/file does not support storing resources');
        }
        $file = new File($id, $this->gaufrette);
        $file->setContent($data);

        return $file;
    }
}
