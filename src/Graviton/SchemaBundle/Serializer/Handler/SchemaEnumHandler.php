<?php
/**
 * SchemaEnumHandler class file
 */

namespace Graviton\SchemaBundle\Serializer\Handler;

use Graviton\SchemaBundle\Document\SchemaEnum;
use JMS\Serializer\Context;
use JMS\Serializer\Visitor\SerializationVisitorInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SchemaEnumHandler
{

    /**
     * Serialize SchemaEnum to JSON
     *
     * @param SerializationVisitorInterface $visitor    Visitor
     * @param SchemaEnum                    $schemaEnum enum
     * @param array                         $type       Type
     * @param Context                       $context    Context
     *
     * @return array
     */
    public function serializeSchemaEnumToJson(
        SerializationVisitorInterface $visitor,
        SchemaEnum $schemaEnum,
        array $type,
        Context $context
    ) {
        return $schemaEnum->getValues();
    }
}
