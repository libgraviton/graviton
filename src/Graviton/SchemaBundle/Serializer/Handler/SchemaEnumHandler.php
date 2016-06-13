<?php
/**
 * SchemaEnumHandler class file
 */

namespace Graviton\SchemaBundle\Serializer\Handler;

use Graviton\SchemaBundle\Document\SchemaEnum;
use JMS\Serializer\Context;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/GPL GPL
 * @link     http://swisscom.ch
 */
class SchemaEnumHandler
{

    /**
     * Serialize SchemaEnum to JSON
     *
     * @param JsonSerializationVisitor $visitor    Visitor
     * @param SchemaEnum               $schemaEnum enum
     * @param array                    $type       Type
     * @param Context                  $context    Context
     *
     * @return array
     */
    public function serializeSchemaEnumToJson(
        JsonSerializationVisitor $visitor,
        SchemaEnum $schemaEnum,
        array $type,
        Context $context
    ) {
        return $schemaEnum->getValues();
    }
}
