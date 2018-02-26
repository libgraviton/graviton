<?php
/**
 * SchemaTypeHandler class file
 */

namespace Graviton\SchemaBundle\Serializer\Handler;

use Graviton\SchemaBundle\Document\SchemaType;
use JMS\Serializer\Context;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SchemaTypeHandler
{

    /**
     * Serialize SchemaType to JSON
     *
     * @param JsonSerializationVisitor $visitor    Visitor
     * @param SchemaType               $schemaType type
     * @param array                    $type       Type
     * @param Context                  $context    Context
     *
     * @return array
     */
    public function serializeSchemaTypeToJson(
        JsonSerializationVisitor $visitor,
        SchemaType $schemaType,
        array $type,
        Context $context
    ) {
        $types = $schemaType->getTypes();

        if (count($types) === 1) {
            return array_pop($types);
        }

        return $types;
    }
}
