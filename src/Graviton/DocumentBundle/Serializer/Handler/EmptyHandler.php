<?php
/**
 * HashHandler class file
 */

namespace Graviton\DocumentBundle\Serializer\Handler;

use JMS\Serializer\Context;
use JMS\Serializer\Exception\NotAcceptableException;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;
use Graviton\DocumentBundle\Entity\Hash;

/**
 * Hash handler for JMS serializer
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class EmptyHandler
{
    /**
     * Serialize Hash object
     *
     * @param JsonSerializationVisitor $visitor Visitor
     * @param Hash                     $data    Data
     * @param array                    $type    Type
     * @param Context                  $context Context
     * @return array
     */
    public function serializeEmptyToJson(
        JsonSerializationVisitor $visitor,
        $data,
        array $type,
        Context $context
    ) {
        if (!$context->shouldSerializeNull()) {
            throw new NotAcceptableException();
        }

        return $visitor->visitNull(null, $type);
    }

    /**
     * Deserialize Hash object
     *
     * @param JsonDeserializationVisitor $visitor Visitor
     * @param array                      $data    Data
     * @param array                      $type    Type
     * @param Context                    $context Context
     *
     * @return Hash|null
     */
    public function deserializeEmptyFromJson(
        JsonDeserializationVisitor $visitor,
        $data,
        array $type,
        Context $context
    ) {
        if (!$context->shouldSerializeNull()) {
            throw new NotAcceptableException();
        }

        return $visitor->visitNull(null, $type);
    }
}
