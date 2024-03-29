<?php
/**
 * ArrayOfObjectSchemaBuilder
 */

namespace Graviton\GeneratorBundle\Schema\Builder;

use Graviton\GeneratorBundle\Schema\SchemaBuilder;
use Graviton\GeneratorBundle\Schema\SchemaBuilderInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ArrayOfObjectSchemaBuilder implements SchemaBuilderInterface
{

    /**
     * gives the SchemaBuilder the opportunity to alter the json schema for that field.
     *
     * @param array $schemaField     the basic field that will be in the schema
     * @param array $fieldDefinition definition as seen by the generator
     * @param array $allDefinitions  all json definitions
     *
     * @return array the altered $schemaField array
     */
    public function buildSchema(array $schemaField, array $fieldDefinition, array $allDefinitions) : array
    {
        $isArray = false;
        $type = $fieldDefinition['schemaType'];

        if (str_ends_with($fieldDefinition['schemaType'], '[]')) {
            $type = substr($type, 0, -2);
            $isArray = true;
        }

        // already done?
        if (isset($schemaField['type']) && $schemaField['type'] == 'array') {
            // already done!
            return $schemaField;
        }

        if (str_starts_with($type, 'class:')) {
            // and take the 2nd one, the bundle name
            $type = sprintf(
                '#/components/schemas/%s',
                SchemaBuilder::getSchemaEntityName($type, $type)
            );
        }

        if ($isArray) {
            $schemaField['type'] = 'array';
            if (str_starts_with($type, '#')) { # ref!
                $schemaField['items'] = ['$ref' => $type];
            } elseif ($type == 'hash') {
                $schemaField['items'] = [
                    'type' => 'object',
                    'additionalProperties' => true
                ];
            } else {
                $schemaField['items'] = ['type' => $type];
            }
        } else {
            if (str_starts_with($type, '#')) { # ref!
                $schemaField['$ref'] = $type;
            } elseif ($type == 'hash') {
                $schemaField['type'] = 'object';
                $schemaField['additionalProperties'] = true;
            } else {
                // ELSE case we DO NOT DO ANYTHING -> leave it to others!
            }
        }

        return $schemaField;
    }
}
