<?php
/**
 * SchemaBuilder
 */

namespace Graviton\GeneratorBundle\Schema;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SchemaBuilder
{
    /**
     * @var SchemaBuilderInterface[]
     */
    private array $builders = [];

    /**
     * Add constraint builder
     *
     * @param SchemaBuilderInterface $builder Constraint builder
     *
     * @return void
     */
    public function addSchemaBuilder(SchemaBuilderInterface $builder)
    {
        $this->builders[] = $builder;
    }

    /**
     * gives the constraintbuilder the opportunity to alter the json schema for that field.
     *
     * @param array $schemaField     the basic field that will be in the schema
     * @param array $fieldDefinition definition as seen by the generator
     *
     * @return array the altered $schemaField array
     */
    public function buildSchema(array $schemaField, array $fieldDefinition) : array
    {
        if (!empty($fieldDefinition['constraints'])) {
            $constraints = [];
            foreach ($fieldDefinition['constraints'] as $constraint) {
                $name = $constraint['name'];
                $options = [];
                foreach ($constraint['options'] as $option) {
                    $options[$option['name']] = $option['value'];
                }
                $constraints[$name] = $options;
            }
            $fieldDefinition['constraints'] = $constraints;
        }

        foreach ($this->builders as $builder) {
            // consolidate constraints a bit
            $schemaField = $builder->buildSchema($schemaField, $fieldDefinition);
        }

        return $schemaField;
    }
}
