<?php
/**
 * EmptySchemaBuilder
 */

namespace Graviton\GeneratorBundle\Schema\Builder;

use Graviton\GeneratorBundle\Schema\SchemaBuilderInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class EmptySchemaBuilder implements SchemaBuilderInterface
{

    /**
     * gives the schemabuilder the opportunity to alter the json schema for that field.
     *
     * @param array $schemaField     the basic field that will be in the schema
     * @param array $fieldDefinition definition as seen by the generator
     *
     * @return array the altered $schemaField array
     */
    public function buildSchema(array $schemaField, array $fieldDefinition) : array
    {
        if (isset($fieldDefinition['constraints']['Empty'])) {
            $schemaField['maxLength'] = 0;
        }

        return $schemaField;
    }
}
