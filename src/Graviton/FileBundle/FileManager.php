<?php
/**
 * Handles file specific actions
 */

namespace Graviton\FileBundle;

use Gaufrette\File;
use Gaufrette\FileSystem;
use Graviton\RestBundle\Model\DocumentModel;
use GravitonDyn\FileBundle\Document\FileMetadata;
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
     * FileManager constructor.
     *
     * @param FileSystem $fileSystem file system abstraction layer for s3 and more
     */
    public function __construct(FileSystem $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    /**
     * Indicates whether the file matching the specified key exists
     *
     * @param string $key
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
     * @param string $key
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
     * @param Request       $request Current Http request
     * @param DocumentModel $model   Model to be used to manage entity
     *
     * @return array
     */
    public function saveFiles(Request $request, DocumentModel $model)
    {
        $inStore = [];
        $files = $this->extractUploadedFiles($request);
        $metaData = json_decode($request->get('metadata'), true);

        foreach ($files as $key => $fileInfo) {
            $entityClass = $model->getEntityClass();
            $record = $model->insertRecord(new $entityClass());
            $inStore[] = $record->getId();

            /** @var \Gaufrette\File $file */
            $file = $this->saveFile($record->getId(), $fileInfo['content']);

            // update record with file metadata
            $meta = new FileMetadata();
            $meta
                ->setSize((int) $file->getSize())
                ->setFilename($fileInfo['data']['filename'])
                ->setMime($fileInfo['data']['mimetype'])
                ->setCreatedate(new \DateTime())
                ->setModificationdate(new \DateTime())
                ->setAction(
                    (!empty($metaData) && array_key_exists('action', $metaData) && !empty($metaData['action']))
                        ? $metaData['action']
                        : []
                );

            $record->setMetadata($meta);
            $model->updateRecord($record->getId(), $record);

            // TODO NOTICE: ONLY UPLOAD OF ONE FILE IS CURRENTLY SUPPORTED
            break;
        }

        return $inStore;
    }

    /**
     * Save or update a file
     *
     * @param Number $id   ID of file
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
}
