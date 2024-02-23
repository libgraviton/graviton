<?php
/**
 * DecimalSchemaBuilder
 */

namespace Graviton\GeneratorBundle\Schema\Builder;

use Graviton\GeneratorBundle\Schema\SchemaBuilderInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class DecimalSchemaBuilder implements SchemaBuilderInterface
{

    /**
     * the pattern to use
     *
     * @var string
     */
    private string $pattern = '^[+\-]?\d+(\.\d+)?$';

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
        if (isset($fieldDefinition['constraints']['Decimal'])) {
            $schemaField['pattern'] = $this->pattern;
        }

        return $schemaField;
    }
}
