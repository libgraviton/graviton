<?php
/**
 * controller for gaufrette based file store
 */

namespace Graviton\FileBundle\Controller;

use Graviton\ExceptionBundle\Exception\MalformedInputException;
use Graviton\FileBundle\Manager\FileManager;
use Graviton\FileBundle\Manager\RequestManager;
use Graviton\RestBundle\Controller\RestController;
use GravitonDyn\FileBundle\Document\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        $file = new File();
        if ($formData = $request->get('metadata')) {
            $file = $this->validateRequest($formData, $this->getModel());
        }

        /** @var RequestManager $requestManager */
        $requestManager = $this->getContainer()->get('graviton.file.request_manager');
        $request = $requestManager->updateFileRequest($request);

        $file = $this->fileManager->handleSaveRequest($file, $request, $this->getModel());

        // Set status code and content
        $response = $this->getResponse();
        $response->setStatusCode(Response::HTTP_CREATED);
        $response->headers->set(
            'Location',
            $this->getRouter()->generate('gravitondyn.file.rest.file.get', array('id' => $file->getId()))
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
        // If a json request, let parent handle it
        if (in_array('application/json', $request->getAcceptableContentTypes())) {
            return parent::getAction($request, $id);
        }

        /** @var File $file */
        $file = $this->findRecord($id);

        /** @var Response $response */
        return $this->fileManager->buildGetContentResponse(
            $this->getResponse(),
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
        // If a json request, let parent handle it
        if ('application/json' == $request->getContentType()) {
            return parent::putAction($id, $request);
        }

        /** @var RequestManager $requestManager */
        $requestManager = $this->getContainer()->get('graviton.file.request_manager');
        $request = $requestManager->updateFileRequest($request);

        $file = new File();
        if ($metadata = $request->get('metadata', false)) {
            $file = $this->validateRequest($metadata, $this->getModel());
        }

        $file = $this->fileManager->handleSaveRequest($file, $request, $this->getModel());

        // Set status code and content
        $response = $this->getResponse();
        $response->setStatusCode(Response::HTTP_NO_CONTENT);
        $response->headers->set(
            'Location',
            $this->getRouter()->generate('gravitondyn.file.rest.file.get', array('id' => $file->getId()))
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
        $response = parent::deleteAction($id);
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
