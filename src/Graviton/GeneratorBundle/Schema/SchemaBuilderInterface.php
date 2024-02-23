<?php
/**
 * SchemaBuilderInterface
 */

namespace Graviton\GeneratorBundle\Schema;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
interface SchemaBuilderInterface
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
    public function buildSchema(array $schemaField, array $fieldDefinition, array $allDefinitions) : array;
}
