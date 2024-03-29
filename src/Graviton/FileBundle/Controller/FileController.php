<?php
/**
 * controller for gaufrette based file store
 */

namespace Graviton\FileBundle\Controller;

use Graviton\FileBundle\Manager\FileManager;
use Graviton\RestBundle\Controller\RestController;
use Graviton\RestBundle\Exception\MalformedInputException;
use GravitonDyn\FileBundle\Document\File;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
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
    private FileManager $fileManager;

    /**
     * @var HttpMessageFactoryInterface
     */
    private HttpMessageFactoryInterface $httpMessageFactory;

    /**
     * On build time we inject the Manager
     *
     * @param FileManager                 $fileManager        Service Manager
     * @param HttpMessageFactoryInterface $httpMessageFactory factory
     *
     * @return void
     */
    public function setComponents(FileManager $fileManager, HttpMessageFactoryInterface $httpMessageFactory)
    {
        $this->fileManager = $fileManager;
        $this->httpMessageFactory = $httpMessageFactory;
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
        $psrRequest = $this->validateAndUniformIncomingRequest($request, $response);

        // get File object
        $file = $this->fileManager->getFileInstance($psrRequest, $this->getModel());

        // Insert the new record
        $this->getModel()->insertRecord($file, $request);

        // Set status code and content
        $response->headers->set(
            'Location',
            $this->getRouter()->generate('File.get', array('id' => $file->getId()))
        );

        return $response;
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

        $psrRequest = $this->validateAndUniformIncomingRequest($request, $response);

        // get merged File instance of existing and PUTted..
        $file = $this->fileManager->getFileInstance($psrRequest, $this->getModel(), $id);

        $this->getModel()->upsertRecord($id, $file, $request);

        // Set status code and content
        $response->headers->set(
            'Location',
            $this->getRouter()->generate('File.get', array('id' => $id))
        );

        return $response;
    }

    /**
     * does stuff when request comes in
     *
     * @param Request  $request  req
     * @param Response $response resp
     * @return ServerRequestInterface psr request
     * @throws \Exception
     */
    private function validateAndUniformIncomingRequest(Request $request, Response $response) : ServerRequestInterface
    {
        // should we do bodychecks or not?
        $psrRequest = $this->fileManager->uniformFileRequest($this->httpMessageFactory->createRequest($request));

        // if the body has *no* metadata, then we skip body checks!
        $hasMetadataBody = $psrRequest->getAttribute('metadataBody', false);

        $this->restUtils->validateRequest($request, $response, $this->getModel(), !$hasMetadataBody);

        return $psrRequest;
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
