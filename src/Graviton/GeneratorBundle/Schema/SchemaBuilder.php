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
     * @param array $allDefinitions  all json definitions
     *
     * @return array the altered $schemaField array
     */
    public function buildSchema(array $schemaField, array $fieldDefinition, array $allDefinitions) : array
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
            $schemaField = $builder->buildSchema($schemaField, $fieldDefinition, $allDefinitions);
        }

        return $schemaField;
    }

    /**
     * get the name for an entity that is used in the openapi schema
     *
     * @param string $shortName        class name
     * @param string $bundleScanString a string containing the bundle name somewhere
     * @return string entity name
     */
    public static function getSchemaEntityName(string $shortName, string $bundleScanString) : string
    {
        if (str_contains($shortName, '\\')) {
            $parts = explode('\\', $shortName);
            $shortName = array_pop($parts);
        }

        preg_match('/([a-zA-Z0-9]*)Bundle/i', $bundleScanString, $matches);

        if (!isset($matches[1])) {
            throw new \RuntimeException('Unable to determine entity name from bundlescanstring: '.$bundleScanString);
        }

        if ($matches[1] != $shortName) {
            return $matches[1].$shortName;
        }

        return $shortName;
    }
}
