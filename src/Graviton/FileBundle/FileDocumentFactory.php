<?php
/**
 * factory to provide File documents
 */

namespace Graviton\FileBundle;

use GravitonDyn\FileBundle\Document\FileMetadataEmbedded;
use GravitonDyn\FileBundle\Document\FileMetadataActionEmbedded;
use GravitonDyn\FileBundle\Document\FileLinksEmbedded;

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
     * @param string $id       Identifier of the file.
     * @param int    $size     Size of the file.
     * @param string $filename Name of the file.
     * @param string $mimetype Mime-Type of the file.
     * @param array  $actions  List of actions to be executed.
     *
     * @return FileMetadata
     */
    public function initiateFileMataData($id, $size, $filename, $mimetype, array $actions = [], $additionalInfo = '')
    {
        $now = new \DateTime();
        $meta = $this->createFileMataData();
        $meta->setId($id);
        $meta
            ->setSize((int) $size)
            ->setFilename($filename)
            ->setMime($mimetype)
            ->setCreatedate($now)
            ->setModificationdate($now)
            ->setAction($actions)
            ->setAdditionalInformation($additionalInfo);

        return $meta;
    }

    /**
     * Provides an instance of FileLinks
     *
     * @return FileLinks
     */
    public function createFileLink()
    {
        return new FileLinksEmbedded();
    }

    /**
     * Sets up a FileLinks instance.
     *
     * @param string $type      Type of the reference
     * @param string $reference Actual reference
     *
     * @return FileLinks
     */
    public function initializeFileLinks($type, $reference)
    {
        $link = $this->createFileLink();
        $link
            ->setRef($reference)
            ->setType($type);

        return $link;
    }
}
