<?php
/**
 * controller for gaufrette based file store
 */

namespace Graviton\FileBundle\Controller;

use Graviton\RestBundle\Controller\RestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Gaufrette\FileSystem;
use Gaufrette\File;
use GravitonDyn\FileBundle\Document\FileMetadata;

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
     * @param FileSystem $gaufrette file system abstraction layer for s3 and more
     */
    public function __construct(Filesystem $gaufrette)
    {
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

        // add file to storage
        $file = new File($record->getId(), $this->gaufrette);
        $file->setContent($request->getContent());

        // update record with file metadata
        $meta = new FileMetadata;
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
            $routeName = substr($routeName, 0, -4) . 'get';
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
     * @param string $id id of file
     *
     * @return Response
     */
    public function getAction(Request $request, $id)
    {
        if (substr(strtolower($request->headers->get('accept')), 0, 16) === 'application/json') {
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
     * Deletes a record
     *
     * @param Number $id ID of record
     *
     * @return Response $response Result of the action
     */
    public function deleteAction($id)
    {
        // @todo remove doc in bucket prior to delegating to parent
        return parent::deleteAction($id);
    }
}
