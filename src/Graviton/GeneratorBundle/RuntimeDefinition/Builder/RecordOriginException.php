<?php
/**
 * RecordOriginException
 */

namespace Graviton\GeneratorBundle\RuntimeDefinition\Builder;

use cebe\openapi\Reader;
use cebe\openapi\spec\Schema;
use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Graviton\GeneratorBundle\RuntimeDefinition\RuntimeDefinitionBuilderAbstract;
use Graviton\RestBundle\Model\RuntimeDefinition;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RecordOriginException extends RuntimeDefinitionBuilderAbstract
{

    /**
     * work on RuntimeDefinition
     *
     * @param RuntimeDefinition $runtimeDefinition runtime def
     * @param JsonDefinition    $definition        definition
     * @param string            $directory         directory
     * @param SplFileInfo       $schemaFile        file info
     */
    public function build(
        RuntimeDefinition $runtimeDefinition,
        JsonDefinition $definition,
        string $directory,
        SplFileInfo $schemaFile
    ) : void {

        $schema = Reader::readFromJsonFile($schemaFile->getPathname());

        $baseObject = $schema->components->schemas[$definition->getId()];

        $fields = $this->iterateFields($baseObject);


        $prefix = '';

        $name = $definition->getId();

        $schema->components->schemas[$definition->getId()];


        $hans = 2;

    }

    public function iterateFields(Schema $schema, array $knownFields = [], string $prefix = '') : array
    {
        $fields = [];

        if (!empty($prefix)) {
            $prefix .= '.';
        }

        foreach ($schema->properties as $fieldName => $property) {
            if ($property->type == 'object') {
                $fields += $this->iterateFields($property, [], $prefix.$fieldName);
                $hans = '';
            } else if ($property->type == 'array') {
                foreach ($property->items as $item) {
                    $fields += $this->iterateFields($item, [], $prefix.$fieldName.'.0');
                }
            } else {
                $fields[$prefix.$fieldName] = $property;
            }
        }

        return $fields;
    }
}
