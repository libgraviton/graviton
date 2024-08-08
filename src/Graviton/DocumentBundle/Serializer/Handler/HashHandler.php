<?php
/**
 * HashHandler class file
 */

namespace Graviton\DocumentBundle\Serializer\Handler;

use JMS\Serializer\Context;
use Graviton\DocumentBundle\Entity\Hash;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;

/**
 * Hash handler for JMS serializer
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class HashHandler
{

    /**
     * Serialize Hash object
     *
     * @param SerializationVisitorInterface $visitor Visitor
     * @param Hash                          $data    Data
     * @param array                         $type    Type
     * @param Context                       $context Context
     * @return Hash
     */
    public function serializeHashToJson(
        SerializationVisitorInterface $visitor,
        Hash $data,
        array $type,
        Context $context
    ) {
        return $data;
    }

    /**
     * Deserialize Hash object
     *
     * @param DeserializationVisitorInterface $visitor Visitor
     * @param array                           $data    Data
     * @param array                           $type    Type
     * @param Context                         $context Context
     * @return Hash
     */
    public function deserializeHashFromJson(
        DeserializationVisitorInterface $visitor,
        $data,
        array $type,
        Context $context
    ) {
        return new Hash($data);
    }
}
