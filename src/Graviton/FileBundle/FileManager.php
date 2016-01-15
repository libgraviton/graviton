<?php
/**
 * Handles file specific actions
 */

namespace Graviton\FileBundle;

use Gaufrette\File;
use Gaufrette\FileSystem;
use Graviton\RestBundle\Model\DocumentModel;
use GravitonDyn\FileBundle\Document\File as FileDocument;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
            $record = $this->createOrUpdateRecord($model, $fileData, $request->get('id'));
            $inStore[] = $record->getId();

            /** @var \Gaufrette\File $file */
            $file = $this->saveFile($record->getId(), $fileInfo['content']);

            $this->initOrUpdateMetadata(
                $record,
                $file->getSize(),
                $fileInfo,
                $fileData
            );

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
            if (0 === $uploadedFile->getError()) {
                $uploadedFiles[$field] = [
                    'data' => [
                        'mimetype' => $uploadedFile->getMimeType(),
                        'filename' => $uploadedFile->getClientOriginalName()
                    ],
                    'content' => file_get_contents($uploadedFile->getPathName())
                ];
            } else {
                throw new UploadException($uploadedFile->getErrorMessage());
            }
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
     * Creates a new or updates an existing instance of the file document
     *
     * @param DocumentModel     $model    Document model
     * @param FileDocument|null $fileData File information
     * @param string            $id       Alternative Id to be checked
     *
     * @return FileDocument
     */
    private function createOrUpdateRecord(DocumentModel $model, FileDocument $fileData = null, $id = '')
    {
        $id = empty($id) && !empty($fileData) ? $fileData->getId() : $id;

        if (($recordExists = empty($record = $model->find($id))) && empty($record = $fileData)) {
            $entityClass = $model->getEntityClass();

            $record = new $entityClass();
        }

        if (!empty($id)) {
            $record->setId($id);
        }

        return $recordExists ? $model->updateRecord($record->getId(), $record) : $model->insertRecord($record);
    }

    /**
     * Updates or initialzes the metadata information of the current entity.
     *
     * @param FileDocument $file     Document to be used
     * @param integer      $fileSize Size of the uploaded file
     * @param array        $fileInfo Additional info about the file
     * @param FileDocument $fileData File data to be updated
     *
     * @return void
     */
    private function initOrUpdateMetaData(FileDocument $file, $fileSize, array $fileInfo, FileDocument $fileData = null)
    {
        if (empty($meta = $file->getMetadata()) && (empty($fileData) || empty($meta = $fileData->getMetadata()))) {
            $meta = $this->fileDocumentFactory->createFileMataData();
            $meta->setId($file->getId());
            $meta->setCreatedate(new \DateTime());
        }

        $meta->setModificationdate(new \DateTime());
        if (empty($meta->getFilename()) && !empty($fileInfo['data']['filename'])) {
            $meta->setFilename($fileInfo['data']['filename']);
        }
        if (!empty($fileInfo['data']['mimetype'])) {
            $meta->setMime($fileInfo['data']['mimetype']);
        }
        $meta->setSize($fileSize);
        $file->setMetadata($meta);
    }

    /**
     * Extracts different information sent in the request content.
     *
     * @param Request $request Current http request
     *
     * @return array
     */
    public function extractDataFromRequestContent(Request $request)
    {
        // split content
        $contentType = $request->headers->get('Content-Type');
        list(, $boundary) = explode('; boundary=', $contentType);

        // fix boundary dash count
        $boundary = '--' . $boundary;

        $content = $request->getContent();
        $contentBlocks = explode($boundary, $content, -1);
        $metadataInfo = '';
        $fileInfo = '';

        // determine content blocks usage
        foreach ($contentBlocks as $contentBlock) {
            if (empty($contentBlock)) {
                continue;
            }
            if (40 === strpos($contentBlock, 'upload')) {
                $fileInfo = $contentBlock;
                continue;
            }
            if (40 === strpos($contentBlock, 'metadata')) {
                $metadataInfo = $contentBlock;
                continue;
            }
        }

        $attributes = array_merge(
            $request->attributes->all(),
            $this->extractMetaDataFromContent($metadataInfo)
        );
        $files = $this->extractFileFromContent($fileInfo);

        return ['files' => $files, 'attributes' => $attributes];
    }

    /**
     * Extracts meta information from request content.
     *
     * @param string $metadataInfoString Information about metadata information
     *
     * @return array
     */
    private function extractMetaDataFromContent($metadataInfoString)
    {
        if (empty($metadataInfoString)) {
            return ['metadata' => '{}'];
        }

        $metadataInfo = explode("\r\n", ltrim($metadataInfoString));
        return ['metadata' => $metadataInfo[2]];
    }

    /**
     * Extracts file data from request content
     *
     * @param string $fileInfoString Information about uploaded files.
     *
     * @return array
     */
    private function extractFileFromContent($fileInfoString)
    {
        if (empty($fileInfoString)) {
            return null;
        }

        $fileInfo = explode("\r\n\r\n", ltrim($fileInfoString), 2);

        // write content to file ("upload_tmp_dir" || sys_get_temp_dir() )
        preg_match('@name=\"([^"]*)\";\sfilename=\"([^"]*)\"\s*Content-Type:\s([^"]*)@', $fileInfo[0], $matches);
        $originalName = $matches[2];
        $dir = ini_get('upload_tmp_dir');
        $dir = (empty($dir)) ? sys_get_temp_dir() : $dir;
        $file = $dir . '/' . $originalName;
        $fileContent = substr($fileInfo[1], 0, -2);

        // create file
        touch($file);
        $size = file_put_contents($file, $fileContent, LOCK_EX);

        $files = [
            $matches[1] => new UploadedFile(
                $file,
                $originalName,
                $matches[3],
                $size
            )
        ];

        return $files;
    }
}
