<?php
/**
 * factory to provide File documents
 */

namespace Graviton\FileBundle;

use GravitonDyn\FileBundle\Document\FileMetadataAction;

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
        return new FileMetadataAction();
    }
}
