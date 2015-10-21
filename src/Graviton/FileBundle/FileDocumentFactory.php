<?php
/**
 * factory to provide File documents
 */

namespace Graviton\FileBundle;

use GravitonDyn\FileBundle\Document\FileMetadataEmbedded;
use GravitonDyn\FileBundle\Document\FileMetadataActionEmbedded;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class FileDocumentFactory
{
    /**
     * Provides an instance of FileMetadataAction
     *
     * @return FileMetadataAction
     */
    public function createFileMetadataAction()
    {
        return new FileMetadataActionEmbedded();
    }

    /**
     * Provides an instance of FileMetadata
     *
     * @return FileMetadata
     */
    public function createFileMataData()
    {
        return new FileMetadataEmbedded();
    }

    /**
     * Provides an instance of FileMetadata
     *
     * @param int    $size     Size of the file.
     * @param string $filename Name of the file.
     * @param string $mimetype Mime-Type of the file.
     * @param array  $actions  List of actions to be executed.
     *
     * @return FileMetadata
     */
    public function initiateFileMataData($size, $filename, $mimetype, array $actions = [])
    {
        $now = new \DateTime();
        $meta = $this->createFileMataData();
        $meta
            ->setSize((int) $size)
            ->setFilename($filename)
            ->setMime($mimetype)
            ->setCreatedate($now)
            ->setModificationdate($now)
            ->setAction($actions);

        return $meta;
    }
}
