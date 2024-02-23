<?php
/**
 * a dummy constraint builder
 */

namespace Graviton\GeneratorBundle\Tests\SchemaBuilder\Builder;

use Graviton\GeneratorBundle\Schema\SchemaBuilderInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class DummyBuilderA implements SchemaBuilderInterface
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
    #[\Override] public function buildSchema(array $schemaField, array $fieldDefinition, array $allDefinitions): array
    {
        $schemaField['title'] = 'THIS WAS SET BY DUMMY-A';
        return $schemaField;
    }
}
