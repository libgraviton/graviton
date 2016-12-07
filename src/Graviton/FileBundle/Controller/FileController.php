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
use GravitonDyn\FileBundle\Model\File as FileModel;
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
     * @var RequestManager
     */
    private $requestManager;

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
     * set RequestManager
     *
     * @param RequestManager $requestManager requestManager
     *
     * @return void
     */
    public function setRequestManager($requestManager)
    {
        $this->requestManager = $requestManager;
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

        $request = $this->requestManager->updateFileRequest($request);

        /** @var FileModel $model */
        $model = $this->getModel();
        $file = $this->fileManager->handleSaveRequest($file, $request, $model);

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
        $request = $this->requestManager->updateFileRequest($request);
        /** @var FileModel $model */
        $model = $this->getModel();

        // Check and wait if another update is being processed
        $this->collectionCache->updateOperationCheck($model->getRepository(), $id);
        $this->collectionCache->addUpdateLock($model->getRepository(), $id, 1);

        $file = new File();
        if ($metadata = $request->get('metadata', false)) {
            $file = $this->validateRequest($metadata, $model);
        }

        $this->collectionCache->addUpdateLock($model->getRepository(), $id, 5);
        $file = $this->fileManager->handleSaveRequest($file, $request, $model);
        $this->collectionCache->releaseUpdateLock($model->getRepository(), $id);

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
            // Checking so update time is correct
            $this->collectionCache->updateOperationCheck($this->getModel()->getRepository(), $id);
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
