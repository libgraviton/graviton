<?php
/**
 * Handles file specific actions
 */

namespace Graviton\FileBundle;

use Gaufrette\File;
use Gaufrette\FileSystem;
use Graviton\ExceptionBundle\Exception\MalformedInputException;
use Graviton\RestBundle\Model\DocumentModel;
use GravitonDyn\FileBundle\Document\File as FileDocument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class FileManager
{
    /**
     * @var FileSystem
     */
    private $fileSystem;

    /**
     * @var FileDocumentFactory
     */
    private $fileDocumentFactory;

    /**
     * FileManager constructor.
     *
     * @param FileSystem          $fileSystem          file system abstraction layer for s3 and more
     * @param FileDocumentFactory $fileDocumentFactory Instance to be used to create action entries.
     */
    public function __construct(FileSystem $fileSystem, FileDocumentFactory $fileDocumentFactory)
    {
        $this->fileSystem = $fileSystem;
        $this->fileDocumentFactory = $fileDocumentFactory;
    }

    /**
     * Indicates whether the file matching the specified key exists
     *
     * @param string $key Identifier to be found
     *
     * @return boolean TRUE if the file exists, FALSE otherwise
     */
    public function has($key)
    {
        return $this->fileSystem->has($key);
    }

    /**
     * Deletes the file matching the specified key
     *
     * @param string $key Identifier to be deleted
     *
     * @throws \RuntimeException when cannot read file
     *
     * @return boolean
     */
    public function delete($key)
    {
        return $this->fileSystem->delete($key);
    }

    /**
     * Reads the content from the file
     *
     * @param  string $key Key of the file
     *
     * @throws \Gaufrette\Exception\FileNotFound when file does not exist
     * @throws \RuntimeException                 when cannot read file
     *
     * @return string
     */
    public function read($key)
    {
        return $this->fileSystem->read($key);
    }

    /**
     * Stores uploaded files to CDN
     *
     * @param Request           $request  Current Http request
     * @param DocumentModel     $model    Model to be used to manage entity
     * @param FileDocument|null $fileData meta information about the file to be stored.
     *
     * @return array
     */
    public function saveFiles(Request $request, DocumentModel $model, FileDocument $fileData = null)
    {
        $inStore = [];
        $files = $this->extractUploadedFiles($request);

        foreach ($files as $key => $fileInfo) {
            /** @var FileDocument $record */
            $record = $this->getRecord($model, $fileData, $request->get('id'));
            $inStore[] = $record->getId();

            /** @var \Gaufrette\File $file */
            $file = $this->saveFile($record->getId(), $fileInfo['content']);

            $meta = $this->initOrUpdateMetadata($record, $file->getSize(), $fileInfo);
            $record->setMetadata($meta);
            $record->setLinks($record->getLinks()->toArray());

            $model->updateRecord($record->getId(), $record);

            // TODO NOTICE: ONLY UPLOAD OF ONE FILE IS CURRENTLY SUPPORTED
            break;
        }

        return $inStore;
    }

    /**
     * Save or update a file
     *
     * @param string $id   ID of file
     * @param String $data content to save
     *
     * @return File
     *
     * @throws BadRequestHttpException
     */
    public function saveFile($id, $data)
    {
        if (is_resource($data)) {
            throw new BadRequestHttpException('/file does not support storing resources');
        }
        $file = new File($id, $this->fileSystem);
        $file->setContent($data);

        return $file;
    }

    /**
     * Moves uploaded files to tmp directory
     *
     * @param Request $request Current http request
     *
     * @return array
     */
    private function extractUploadedFiles(Request $request)
    {
        $uploadedFiles = [];

        /** @var  $uploadedFile \Symfony\Component\HttpFoundation\File\UploadedFile */
        foreach ($request->files->all() as $field => $uploadedFile) {
            $movedFile = $uploadedFile->move('/tmp/');
            $uploadedFiles[$field] = [
                'data' => [
                    'mimetype' => $uploadedFile->getMimeType(),
                    'filename' => $uploadedFile->getClientOriginalName()
                ],
                'content' => file_get_contents($movedFile)
            ];

            // delete moved file from /tmp
            unlink($movedFile->getRealPath());
        }

        if (empty($uploadedFiles)) {
            $uploadedFiles['upload'] = [
                'data' => [
                    'mimetype' => $request->headers->get('Content-Type'),
                    'filename' => ''
                ],
                'content' => $request->getContent()
            ];
        }

        return $uploadedFiles;
    }

    /**
     * Provides a set up instance of the file document
     *
     * @param DocumentModel     $model    Document model
     * @param FileDocument|null $fileData File information
     * @param string            $id       Alternative Id to be checked
     *
     * @return FileDocument
     */
    private function getRecord(DocumentModel $model, FileDocument $fileData = null, $id = '')
    {
        // does it really exist??
        if (!empty($fileData)) {
            $record = $model->find($fileData->getId());
        } elseif (!empty($id)) {
            $record = $model->find($id);
        }

        if (!empty($record)) {
            // handle missing 'id' field in input to a PUT operation
            // if it is settable on the document, let's set it and move on.. if not, inform the user..
            if ($record->getId() != $id) {
                // try to set it..
                if (is_callable(array($fileData, 'setId'))) {
                    $record->setId($id);
                } else {
                    throw new MalformedInputException('No ID was supplied in the request payload.');
                }
            }

            return $model->updateRecord($id, $record);
        }

        if (!empty($fileData)) {
            $record = $fileData;
        } else {
            $entityClass = $model->getEntityClass();
            $record = new $entityClass();
        }

        return $model->insertRecord($record);
    }

    /**
     * Extracts meta information from given file.
     *
     * @param FileDocument $file File instance to extract the data from.
     *
     * @return array
     */
    private function extractMetadata(FileDocument $file)
    {
        $metaData = $file->getMetadata();
        if (!empty($metaData)) {
            return $metaData->getAction()->toArray();
        }

        return [];
    }

    /**
     * Provides a set up FileMetaData instance
     *
     * @param FileDocument $file     Document to be used
     * @param integer      $fileSize Size of the uploaded file
     * @param array        $fileInfo Additinoal info about the file
     *
     * @return \GravitonDyn\FileBundle\Document\FileMetadata
     */
    private function initOrUpdateMetadata(FileDocument $file, $fileSize, array $fileInfo)
    {
        $meta = $file->getMetadata();
        if (!empty($meta)) {
            $meta
                ->setAction($this->extractMetadata($file))
                ->setSize((int) $fileSize)
                ->setMime($fileInfo['data']['mimetype'])
                ->setModificationdate(new \DateTime());
        } else {
            // update record with file metadata
            $meta = $this->fileDocumentFactory->initiateFileMataData(
                $file->getId(),
                (int) $fileSize,
                $fileInfo['data']['filename'],
                $fileInfo['data']['mimetype'],
                $this->extractMetadata($file)
            );
        }

        return $meta;
    }
}
