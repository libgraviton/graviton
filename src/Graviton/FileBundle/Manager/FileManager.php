<?php
/**
 * Handles file specific actions
 */

namespace Graviton\FileBundle\Manager;

use Doctrine\ODM\MongoDB\Id\UuidGenerator;
use GravitonDyn\FileBundle\Document\File as FileDocument;
use GravitonDyn\FileBundle\Document\FileMetadataBase;
use GravitonDyn\FileBundle\Document\FileMetadataEmbedded;
use League\Flysystem\Filesystem;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use GravitonDyn\FileBundle\Document\File as DocumentFile;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Filesystem\Filesystem as SfFileSystem;
use GravitonDyn\FileBundle\Model\File as DocumentModel;
use Graviton\ExceptionBundle\Exception\NotFoundException;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class FileManager
{
    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * @var array allowedMimeTypes Control files to be saved and returned
     */
    private $allowedMimeTypes = [];

    /**
     * @var bool whether or not we should read the files mimetype or trust the database (depending on storage)
     */
    private $readFileSystemMimeType = false;

    /**
     * FileManager constructor.
     *
     * @param Filesystem      $fileSystem      file system abstraction layer for s3 and more
     * @param ManagerRegistry $managerRegistry MongoDB registry manager
     */
    public function __construct(
        Filesystem $fileSystem,
        ManagerRegistry $managerRegistry
    ) {
        $this->fileSystem = $fileSystem;
        $this->documentManager = $managerRegistry->getManager();
    }

    /**
     * Configure allowed content types, empty is equal to all
     *
     * @param array $mimeTypes of Allowed types, application/pdf, image/jpeg...
     *
     * @return void
     */
    public function setAllowedMimeTypes(array $mimeTypes)
    {
        $this->allowedMimeTypes = $mimeTypes;
    }

    /**
     * set ReadFileSystemMimeType
     *
     * @param bool $readFileSystemMimeType readFileSystemMimeType
     *
     * @return void
     */
    public function setReadFileSystemMimeType($readFileSystemMimeType)
    {
        $this->readFileSystemMimeType = $readFileSystemMimeType;
    }

    /**
     * Will update the response object with provided file data
     *
     * @param Response     $response response
     * @param DocumentFile $file     File document object from DB
     *
     * @return Response
     * @throws InvalidArgumentException if invalid info fetched from fileSystem
     */
    public function buildGetContentResponse(Response $response, FileDocument $file)
    {
        /** @var FileMetadataBase $metadata */
        $metadata = $file->getMetadata();
        if (!$metadata) {
            throw new InvalidArgumentException('Loaded file have no valid metadata');
        }

        // We use file's mimeType, just in case none we use DB's.
        $mimeType = null;

        if ($this->readFileSystemMimeType) {
            $mimeType = $this->fileSystem->getMimetype($file->getId());
        }
        if (!$mimeType) {
            $mimeType = $metadata->getMime();
        }
        if ($this->allowedMimeTypes && !in_array($mimeType, $this->allowedMimeTypes)) {
            throw new InvalidArgumentException('File mime type: '.$mimeType.' is not allowed as response.');
        }

        // Create Response
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $metadata->getFilename()
        );

        $response
            ->setStatusCode(Response::HTTP_OK)
            ->setContent($this->fileSystem->read($file->getId()));
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
    public function saveFile($id, $filepath)
    {
        // will save using a stream
        $fp = fopen($filepath, 'r+');

        $this->fileSystem->putStream($id, $fp);

        // close file
        fclose($fp);
    }

    /**
     * @param DocumentFile  $document File Document
     * @param Request       $request  Request bag
     * @param DocumentModel $model    File Document Model
     * @return DocumentFile
     */
    public function handleSaveRequest(
        FileDocument $document,
        Request $request,
        DocumentModel $model
    ) {
        $file = $this->getUploadedFileFromRequest($request);
        $requestId = $request->get('id', '');
        if ($requestId && !$document->getId()) {
            $document->setId($requestId);
        }

        try {
            $original = $model->find($requestId, true);
        } catch (NotFoundException $e) {
            $original = false;
        }

        $isNew = $requestId ? !$original : true;

        // If posted  file document not equal the one to be created or updated, then error
        if (!$this->validIdRequest($document, $requestId)) {
            throw new InvalidArgumentException('File id and Request id must match.');
        }

        $document = $this->buildFileDocument($document, $file, $original);
        if (!$document->getId()) {
            $n = new UuidGenerator();
            $uuid = (string) $n->generateV4();
            $document->setId($uuid);
        }

        // Filename limitation
        if ($filename = $document->getMetadata()->getFilename()) {
            // None English chars
            if (preg_match('/[^a-z_\-0-9.]/i', $filename)) {
                throw new InvalidArgumentException('None special chars allowed for filename, given: '.$filename);
            }
        }

        // All ok, let's save the file
        if ($isNew) {
            if (!$file || $file->getSize() == 0) {
                throw new InvalidArgumentException('You can not create a new empty file resource. No file received.');
            }
        }

        if ($file) {
            $this->saveFile($document->getId(), $file->getRealPath());
            $sfFileSys = new SfFileSystem();
            $sfFileSys->remove($file->getRealPath());
        }

        if ($isNew) {
            $model->insertRecord($document);
        } else {
            $model->updateRecord($document->getId(), $document);
        }

        // store id of new record so we don't need to re-parse body later when needed
        $request->attributes->set('id', $document->getId());

        return $document;
    }

    /**
     * Create the basic needs for a file
     *
     * @param DocumentFile $document Post or Put file document
     * @param UploadedFile $file     To be used in set metadata
     * @param DocumentFile $original If there is a original document
     *
     * @return DocumentFile
     * @throws InvalidArgumentException
     */
    private function buildFileDocument(FileDocument $document, $file, $original)
    {
        $now = new \DateTime();

        // If only a file is posted, check if there is a original object and clone it
        if ($file && $original && !$document->getMetadata()) {
            $document = clone $original;
        }

        // Basic Metadata update
        $metadata = $document->getMetadata() ?: new FileMetadataEmbedded();

        // File related, if no file uploaded we keep original file info.
        if ($file) {
            $hash = $metadata->getHash();
            if (!$hash || strlen($hash)>64) {
                $hash = hash('sha256', file_get_contents($file->getRealPath()));
            } else {
                $hash = preg_replace('/[^a-z0-9_-]/i', '-', $hash);
            }
            $metadata->setHash($hash);
            $metadata->setMime($file->getMimeType());

            // special case -> if determined mime type is json, we need to change it..
            if ($metadata->getMime() == 'application/json') {
                $metadata->setMime('text/plain');
            }

            $metadata->setSize($file->getSize());
            if (!$metadata->getFilename()) {
                $fileName = $file->getClientOriginalName() ? $file->getClientOriginalName() : $file->getFilename();
                $fileName = preg_replace("/[^a-zA-Z0-9.]/", "-", $fileName);
                $metadata->setFilename($fileName);
            }
        } elseif ($original && ($originalMetadata = $original->getMetadata())) {
            if (!$metadata->getFilename()) {
                $metadata->setFilename($originalMetadata->getFilename());
            }
            $metadata->setHash($originalMetadata->getHash());
            $metadata->setMime($originalMetadata->getMime());
            $metadata->setSize($originalMetadata->getSize());
        }

        // Creation date. keep original if available
        if ($original && $original->getMetadata() && $original->getMetadata()->getCreatedate()) {
            $metadata->setCreatedate($original->getMetadata()->getCreatedate());
        } else {
            $metadata->setCreatedate($now);
        }

        $metadata->setModificationdate($now);
        $document->setMetadata($metadata);

        return $document;
    }

    /**
     * Simple validation for post/put request
     *
     * @param DocumentFile $document  File document
     * @param string       $requestId Request ID
     * @return bool
     */
    private function validIdRequest(FileDocument $document, $requestId)
    {
        if (!$requestId && !$document->getId()) {
            return true;
        }
        if ($requestId === $document->getId()) {
            return true;
        }
        return false;
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
        if ($this->fileSystem->has($id)) {
            $this->fileSystem->delete($id);
        }
    }

    /**
     * Set global uploaded file.
     * Only ONE file allowed per upload.
     *
     * @param Request $request service request
     * @return UploadedFile if file was uploaded
     * @throws InvalidArgumentException
     */
    private function getUploadedFileFromRequest(Request $request)
    {
        $file = false;

        if ($request->files instanceof FileBag && $request->files->count() > 0) {
            if ($request->files->count() > 1) {
                throw new InvalidArgumentException('Only 1 file upload per requests allowed.');
            }
            $files = $request->files->all();
            $file = reset($files);
            if ($this->allowedMimeTypes && !in_array($file->getMimeType(), $this->allowedMimeTypes)) {
                throw new InvalidArgumentException('File mime type: '.$file->getMimeType().' is not allowed.');
            }
        }

        return $file;
    }
}
