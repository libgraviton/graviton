<?php
/**
 * ArrayObjectHandler class file
 */

namespace Graviton\GeneratorBundle\Serializer\Handler;

use JMS\Serializer\Context;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * ArrayObject handler
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ArrayObjectHandler
{
    /**
     * Serialize ArrayObject
     *
     * @param JsonSerializationVisitor $visitor Visitor
     * @param \ArrayObject             $data    Data
     * @param array                    $type    Type
     * @param Context                  $context Context
     * @return \ArrayObject
     */
    public function serializeArrayObjectToJson(
        JsonSerializationVisitor $visitor,
        \ArrayObject $data,
        array $type,
        Context $context
    ) {
        return new \ArrayObject($visitor->visitArray($data->getArrayCopy(), $type, $context));
    }

    /**
     * Deserialize ArrayObject
     *
     * @param JsonDeserializationVisitor $visitor Visitor
     * @param array                      $data    Data
     * @param array                      $type    Type
     * @param Context                    $context Context
     * @return \ArrayObject
     */
    public function deserializeArrayObjectFromJson(
        JsonDeserializationVisitor $visitor,
        array $data,
        array $type,
        Context $context
    ) {
        return new \ArrayObject($visitor->visitArray($data, $type, $context));
    }
}
