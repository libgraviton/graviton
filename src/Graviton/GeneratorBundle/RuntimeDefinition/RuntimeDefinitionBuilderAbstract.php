<?php
/**
 * RuntimeDefinitionBuilderAbstract
 */

namespace Graviton\GeneratorBundle\RuntimeDefinition;

use cebe\openapi\Reader;
use cebe\openapi\spec\Schema;
use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Graviton\GeneratorBundle\RuntimeDefinition\Builder\RuntimeBuilderData;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
abstract class RuntimeDefinitionBuilderAbstract
{
    /**
     * work on RuntimeDefinition
     *
     * @param RuntimeBuilderData $data data
     */
    abstract public function build(RuntimeBuilderData $data) : void;

    /**
     * Gets the base Schema file of a definition
     *
     * @param JsonDefinition $definition definition
     * @param SplFileInfo    $schemaFile schemafile
     *
     * @return void
     */
    public function getSchemaBaseObject(JsonDefinition $definition, SplFileInfo $schemaFile) : Schema
    {
        $schema = Reader::readFromJsonFile($schemaFile->getPathname());
        return $schema->components->schemas[$definition->getId()];
    }

    /**
     * gets flat array of all fields in the service definition
     *
     * @param Schema $schema schema definition
     * @param string $prefix name prefix
     *
     * @return Schema[] all fields
     */
    public function getAllFields(Schema $schema, string $prefix = '') : array
    {
        $fields = [];

        if (!empty($prefix)) {
            $prefix .= '.';
        }

        if (empty($schema->properties) && $schema->additionalProperties === true) {
            // strip trailing / if there
            if (str_ends_with($prefix, '.')) {
                $prefix = substr($prefix, 0, -1);
            }

            return [
                $prefix => $schema,
                $prefix.'.*' => $schema
            ];
        }

        foreach ($schema->properties as $fieldName => $property) {
            if ($property->type == 'object') {
                $fields += $this->getAllFields($property, $prefix.$fieldName);
            } else if ($property->type == 'array') {
                if (is_array($property->items)) {
                    foreach ($property->items as $item) {
                        $fields += $this->getAllFields($item, $prefix.$fieldName.'.0');
                    }
                } else if (is_string($property->items->type) && $property->items->type == 'object') {
                    $fields += $this->getAllFields($property->items, $prefix.$fieldName.'.0');
                } else if (is_string($property->items->type)) {
                    $fields[$prefix.$fieldName.'.0'] = $property;
                }
            } else {
                $fields[$prefix.$fieldName] = $property;
            }
        }

        return $fields;
    }
}
