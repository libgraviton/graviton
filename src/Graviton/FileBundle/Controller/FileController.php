<?php
/**
 * controller for gaufrette based file store
 */

namespace Graviton\FileBundle\Controller;

use Graviton\RestBundle\Controller\RestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Gaufrette\FileSystem;

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

        // @todo add new file to bucket and store ref in request doc

        $entityClass = $this->getModel()->getEntityClass();
        $record = new $entityClass;

        // Insert the new record
        $record = $this->getModel()->insertRecord($record);

        // store id of new record so we dont need to reparse body later when needed
        $request->attributes->set('id', $record->getId());

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

        $response = parent::postAction($request);

        var_dump($response);

        return $response;
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
        // @todo update file in bucket and update ref in request document
        return parent::putAction($id, $request);
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
