<?php
/**
 * controller for gaufrette based file store
 */

namespace Graviton\FileBundle\Controller;

use Graviton\ExceptionBundle\Exception\MalformedInputException;
use Graviton\FileBundle\Manager\FileManager;
use Graviton\RestBundle\Controller\RestController;
use GravitonDyn\FileBundle\Document\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class FileController extends RestController
{
    /**
     * @var FileManager
     */
    private $fileManager;

    /**
     * On build time we inject the Manager
     *
     * @param FileManager $fileManager Service Manager
     *
     * @return void
     */
    public function setFileManager(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    /**
     * Writes a new Entry to the database
     * Can accept either direct Post data or Form upload
     *
     * @param Request $request Current http request
     *
     * @return Response $response Result of action with data (if successful)
     */
    public function postAction(Request $request)
    {
        $response = new Response('', Response::HTTP_CREATED);

        // validate request!
        $psrRequest = $this->restUtils->validateRequest($request, $response, $this->getModel());

        // uniform
        $psrRequest = $this->fileManager->uniformFileRequest($psrRequest);

        // get File object
        $file = $this->fileManager->getFileInstance($psrRequest, $this->getModel());

        // Insert the new record
        $this->getModel()->insertRecord($file);

        // Set status code and content
        $response->headers->set(
            'Location',
            $this->getRouter()->generate('File.get', array('id' => $file->getId()))
        );

        $request->attributes->set('id', $file->getId());

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
        // If a json request, let parent handle it
        if (in_array('application/json', $request->getAcceptableContentTypes())) {
            return parent::getAction($request, $id);
        }

        /** @var File $file */
        $file = $this->getModel()->find($id);

        /** @var Response $response */
        return $this->fileManager->buildGetContentResponse(
            $file
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
        // validate first
        $response = new Response('', Response::HTTP_NO_CONTENT);
        $psrRequest = $this->restUtils->validateRequest($request, $response, $this->getModel());

        // uniform it..
        $psrRequest = $this->fileManager->uniformFileRequest($psrRequest);

        // get merged File instance of existing and PUTted..
        $file = $this->fileManager->getFileInstance($psrRequest, $this->getModel(), $id);

        $this->getModel()->upsertRecord($id, $file);

        $this->addRequestAttributes($request);
        $request->attributes->set('id', $id);

        // Set status code and content
        $response->headers->set(
            'Location',
            $this->getRouter()->generate('File.get', array('id' => $id))
        );

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
        $response = parent::deleteAction($id, $request);
        $this->fileManager->remove($id);
        return $response;
    }

    /**
     * Patch a record, we add here a patch on Modification Data.
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
        // Update modified date
        $content = json_decode($request->getContent(), true);
        if ($content) {
            // Checking so update time is correct
            $now = new \DateTime();
            $patch = [
                'op' => 'replace',
                'path' => '/metadata/modificationDate',
                'value' => $now->format(DATE_ISO8601)
            ];
            // It can be a simple patch or a multi array patching.
            if (array_key_exists(0, $content)) {
                $content[] = $patch;
            } else {
                $content = [$content, $patch];
            }

            $request = new Request(
                $request->query->all(),
                $request->request->all(),
                $request->attributes->all(),
                $request->cookies->all(),
                $request->files->all(),
                $request->server->all(),
                json_encode($content)
            );
        }

        return parent::patchAction($id, $request);
    }
}
