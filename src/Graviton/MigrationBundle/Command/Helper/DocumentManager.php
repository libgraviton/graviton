<?php
/**
 * helper to access document manager in commands
 */

namespace Graviton\MigrationBundle\Command\Helper;

use Symfony\Component\Console\Helper\HelperInterface;
use Symfony\Component\Console\Helper\Helper;
use Doctrine\ODM\MongoDB\DocumentManager as DoctrineDocumentManager;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class DocumentManager extends Helper implements HelperInterface
{
    /**
     * @var DoctrineDocumentManager
     */
    private $documentManager;

    /**
     * @param DoctrineDocumentManager $documentManager document manager for console apps
     */
    public function __construct(DoctrineDocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'dm';
    }

    /**
     * @return DoctrineDocumentManager
     */
    public function getDocumentManager()
    {
        return $this->documentManager;
    }
}
