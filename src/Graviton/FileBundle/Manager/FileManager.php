<?php
/**
 * handles file stuff
 */

namespace Graviton\FileBundle\Manager;

use Ckr\Util\ArrayMerger;
use Graviton\RestBundle\Model\DocumentModel;
use Graviton\RestBundle\Service\RestUtils;
use GravitonDyn\FileBundle\Document\File;
use GravitonDyn\FileBundle\Document\FileMetadataBase;
use GravitonDyn\FileBundle\Document\FileMetadataEmbedded;
use Http\Discovery\Psr17Factory;
use League\Flysystem\Filesystem;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Riverline\MultiPartParser\Converters\PSR7;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
readonly class FileManager
{
    public function __construct(
        private Filesystem $fileSystem,
        private RestUtils $restUtils,
        private Psr17Factory $psr17Factory,
        private array $allowedMimeTypes = []
    ) {
    }

    /**
     * brings the request in a simple form to deal with it
     *
     * @param ServerRequestInterface $request request
     *
     * @return ServerRequestInterface parsed request
     */
    public function uniformFileRequest(ServerRequestInterface $request) : ServerRequestInterface
    {
        $contentType = strtolower($request->getHeaderLine('content-type'));

        // as-is
        if (str_contains($contentType, 'application/json')) {
            return $request->withAttribute('metadataBody', true);
        }

        if (str_contains($contentType, 'multipart')) {
            $part = PSR7::convert($request);

            // json -> is body!
            $metadata = $part->getPartsByName('metadata');
            if (!empty($metadata[0])) {
                $request = $request->withBody($this->psr17Factory->createStream($metadata[0]->getBody()));
            }

            // file upload
            $upload = $part->getPartsByName('upload');
            if (!empty($upload[0])) {
                $request = $request->withUploadedFiles(
                    [
                        'upload' => $this->psr17Factory->createUploadedFile(
                            $this->psr17Factory->createStream($upload[0]->getBody()),
                            clientFilename: $upload[0]->getFileName(),
                            clientMediaType: $upload[0]->getMimeType()
                        )
                    ]
                );
            }

            $request = $request->withAttribute('metadataBody', true);
        } else {
            // change body!
            $parsedBody = $request->getParsedBody();
            if (is_array($parsedBody) && isset($parsedBody['metadata'])) {
                $request = $request
                    ->withBody($this->psr17Factory->createStream($parsedBody['metadata']))
                    ->withParsedBody(null)
                    ->withAttribute('metadataBody', true);
            } else {
                $request = $request->withUploadedFiles(
                    [
                        'upload' => $this->psr17Factory->createUploadedFile(
                            $request->getBody()
                        )
                    ]
                )->withBody($this->psr17Factory->createStream('{}'))
                 ->withAttribute('metadataBody', true);
            }
        }
        return $request;
    }

    /**
     * Will update the response object with provided file data
     *
     * @param File $file File document object from DB
     *
     * @return Response response
     *
     * @throws InvalidArgumentException if invalid info fetched from fileSystem
     */
    public function buildGetContentResponse(File $file) : Response
    {
        /** @var FileMetadataBase $metadata */
        $metadata = $file->getMetadata();
        if (!$metadata) {
            throw new InvalidArgumentException('Loaded file have no valid metadata');
        }

        $fileStream = $this->fileSystem->readStream($file->getId());

        // read metadata
        $mimeType = mime_content_type($fileStream);

        if (!empty($this->allowedMimeTypes) && !in_array($mimeType, $this->allowedMimeTypes)) {
            throw new InvalidArgumentException('File mime type: '.$mimeType.' is not allowed as response.');
        }

        $response = new StreamedResponse(
            function () use ($fileStream) {
                echo stream_get_contents($fileStream);
            }
        );

        // Create Response
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $metadata->getFilename()
        );

        $response
            ->setStatusCode(Response::HTTP_OK);
        $response
            ->headers->set('Content-Type', $mimeType);
        $response
            ->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * Save or update a file
     *
     * @param string $id       ID of file
     * @param String $filepath path to the file to save
     *
     * @return void
     */
    private function applyUploadMetadata(File $file, UploadedFileInterface $uploadedFile)
    {
        $fileResource = $uploadedFile->getStream()->detach();

        $metadata = $file->getMetadata();
        if (is_null($metadata)) {
            $metadata = new FileMetadataEmbedded();
        }

        if (!empty($uploadedFile->getSize())) {
            $metadata->setSize($uploadedFile->getSize());
        }

        if (empty($metadata->getFilename())) {
            if (!empty($uploadedFile->getClientFilename())) {
                $metadata->setFilename($uploadedFile->getClientFilename());
            } else {
                $metadata->setFilename($file->getId());
            }
        }

        if (!empty($uploadedFile->getClientFilename())) {
            $metadata->setFilename($uploadedFile->getClientFilename());
        }

        if (empty($metadata->getCreatedate())) {
            $metadata->setCreatedate(new \DateTime());
        }
        $metadata->setModificationdate(new \DateTime());

        // hash
        $ctx = hash_init('sha256');
        rewind($fileResource);
        hash_update_stream($ctx, $fileResource);
        $hash = hash_final($ctx);
        if (!empty($hash)) {
            $metadata->setHash($hash);
        }

        rewind($fileResource);

        // read and set mimetype
        $mimeType = mime_content_type($fileResource);
        if (!empty($mimeType)) {
            $metadata->setMime($mimeType);
        }

        $file->setMetadata($metadata);

        $this->fileSystem->writeStream($file->getId(), $fileResource);
    }

    public function getFileInstance(ServerRequestInterface $request, DocumentModel $model, ?string $id = null) : object
    {
        $payload = (string) $request->getBody();
        if (empty($payload)) {
            $payload = '{}';
        }

        // existing?
        if (!empty($id)) {
            try {
                $existing = $model->getSerialised($id);
                if (!empty($existing)) {
                    $existingArr = \json_decode($existing, true);
                    $newArr = \json_decode($payload, true);

                    // arrays that take precedence
                    if (isset($newArr['links'])) {
                        unset($existingArr['links']);
                    }
                    if (isset($newArr['metadata']) && isset($newArr['metadata']['action'])) {
                        unset($existingArr['metadata']['action']);
                    }
                    if (isset($newArr['metadata']) && isset($newArr['metadata']['additionalProperties'])) {
                        unset($existingArr['metadata']['additionalProperties']);
                    }

                    $mergerFlags = ArrayMerger::FLAG_OVERWRITE_NUMERIC_KEY;
                    $payload = ArrayMerger::doMerge(
                        $existingArr,
                        $newArr,
                        $mergerFlags
                    );
                    // encode it again!
                    $payload = \json_encode($payload);
                }
            } catch (\Throwable $t) {
            }
        }

        $file = $this->restUtils->deserializeContent(
            $payload,
            File::class
        );

        if (empty($file->getId())) {
            $file->setId($this->getRecordId());
        }

        if (!empty($id)) {
            $file->setId($id);
        }

        // data from upload?
        if (isset($request->getUploadedFiles()['upload'])) {
            $this->applyUploadMetadata(
                $file,
                $request->getUploadedFiles()['upload']
            );
        }

        return $file;
    }

    private function getRecordId() : string
    {
        return str_repeat(str_replace('.', '', uniqid('', true)), 2);
    }

    /**
     * Simple delete item from file system
     *
     * @param string $id ID of file to be deleted
     *
     * @return void
     */
    public function remove($id)
    {
        if ($this->fileSystem->fileExists($id)) {
            $this->fileSystem->delete($id);
        }
    }
}
