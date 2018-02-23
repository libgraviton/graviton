<?php
/**
 * SchemaAdditionalPropertiesHandler class file
 * adds the flexibility of render 'additionalProperties' in schema/swagger.json
 * in a proper way depending of the consumer
 */

namespace Graviton\SchemaBundle\Serializer\Handler;

use Graviton\SchemaBundle\Document\Schema;
use Graviton\SchemaBundle\Document\SchemaAdditionalProperties;
use JMS\Serializer\Context;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SchemaAdditionalPropertiesHandler
{

    /**
     * Serialize additionalProperties to JSON
     *
     * @param JsonSerializationVisitor   $visitor              Visitor
     * @param SchemaAdditionalProperties $additionalProperties properties
     * @param array                      $type                 Type
     * @param Context                    $context              Context
     *
     * @return string|null
     */
    public function serializeSchemaAdditionalPropertiesToJson(
        JsonSerializationVisitor $visitor,
        SchemaAdditionalProperties $additionalProperties,
        array $type,
        Context $context
    ) {

        $properties = $additionalProperties->getProperties();

        // case for v4 schema
        if (is_bool($properties)) {
            return $visitor->visitBoolean($properties, [], $context);
        }

        // case for schema inside additionalProperties, swagger exclusive
        if ($properties instanceof Schema) {
            return $visitor->getNavigator()->accept(
                $properties,
                ['name' => 'Graviton\SchemaBundle\Document\Schema'],
                $context
            );
        }
    }
}
