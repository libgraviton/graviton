<?php
/**
 * RangeSchemaBuilder
 */

namespace Graviton\GeneratorBundle\Schema\Builder;

use Graviton\GeneratorBundle\Schema\SchemaBuilderInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RangeSchemaBuilder implements SchemaBuilderInterface
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
        if (isset($fieldDefinition['constraints']['Range'])) {
            $options = $fieldDefinition['constraints']['Range'];
            if (isset($options['min'])) {
                $schemaField['minimum'] = floatval($options['min']);
            }
            if (isset($options['max'])) {
                $schemaField['maximum'] = floatval($options['max']);
            }
        }

        if (isset($fieldDefinition['constraints']['GreaterThanOrEqual'])) {
            $options = $fieldDefinition['constraints']['GreaterThanOrEqual'];
            if (isset($options['value'])) {
                $schemaField['minimum'] = floatval($options['value']);
            }
        }

        if (isset($fieldDefinition['constraints']['LessThanOrEqual'])) {
            $options = $fieldDefinition['constraints']['LessThanOrEqual'];
            if (isset($options['value'])) {
                $schemaField['maximum'] = floatval($options['value']);
            }
        }

        return $schemaField;
    }
}
