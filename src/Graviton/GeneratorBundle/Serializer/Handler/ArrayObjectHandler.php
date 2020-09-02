<?php
/**
 * ArrayObjectHandler class file
 */

namespace Graviton\GeneratorBundle\Serializer\Handler;

use JMS\Serializer\Context;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;

/**
 * ArrayObject handler
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ArrayObjectHandler
{
    /**
     * Serialize ArrayObject
     *
     * @param SerializationVisitorInterface $visitor Visitor
     * @param \ArrayObject                  $data    Data
     * @param array                         $type    Type
     * @param Context                       $context Context
     * @return \ArrayObject
     */
    public function serializeArrayObjectToJson(
        SerializationVisitorInterface $visitor,
        \ArrayObject $data,
        array $type,
        Context $context
    ) {
        return new \ArrayObject($visitor->visitArray($data->getArrayCopy(), $type, $context));
    }

    /**
     * Deserialize ArrayObject
     *
     * @param DeserializationVisitorInterface $visitor Visitor
     * @param array                           $data    Data
     * @param array                           $type    Type
     * @param Context                         $context Context
     * @return \ArrayObject
     */
    public function deserializeArrayObjectFromJson(
        DeserializationVisitorInterface $visitor,
        array $data,
        array $type,
        Context $context
    ) {
        return new \ArrayObject($visitor->visitArray($data, $type, $context));
    }
}
