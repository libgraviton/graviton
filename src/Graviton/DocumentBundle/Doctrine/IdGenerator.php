<?php
/**
 * Doctrine IdGenerator
 */

namespace Graviton\DocumentBundle\Doctrine;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Id\AbstractIdGenerator;
use MongoDB\BSON\ObjectId;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class IdGenerator extends AbstractIdGenerator
{

    /**
     * Generates an identifier for a document.
     *
     * @return mixed
     */
    public function generate(DocumentManager $dm, object $document)
    {
        return (string) new ObjectId();

        // TODO: Implement generate() method.
    }
}

