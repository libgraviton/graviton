<?php
/**
 * RecordOriginException
 */

namespace Graviton\GeneratorBundle\RuntimeDefinition\Builder;

use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Graviton\GeneratorBundle\RuntimeDefinition\RuntimeDefinitionBuilderAbstract;
use Graviton\RestBundle\Model\RuntimeDefinition;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class FieldFlagsBuilder extends RuntimeDefinitionBuilderAbstract
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

        $baseSchema = $this->getSchemaBaseObject($definition, $schemaFile);
        $fields = $this->getAllFields($baseSchema);

        $recordOriginExceptionFields = [];
        $readOnlyFields = [];
        $incrementalDateFields = [];

        foreach ($fields as $path => $field) {
            if (isset($field->{'x-recordOriginException'}) && $field->{'x-recordOriginException'} === true) {
                $recordOriginExceptionFields[] = $path;
            }

            if (isset($field->{'x-readOnly'}) && $field->{'x-readOnly'} === true) {
                $readOnlyFields[] = $path;
            }

            if (isset($field->{'x-incrementalDate'}) && $field->{'x-incrementalDate'} === true) {
                $incrementalDateFields[] = $path;
            }
        }

        $runtimeDefinition->setRecordOriginExceptionFields($recordOriginExceptionFields);
        $runtimeDefinition->setReadOnlyFields($readOnlyFields);
        $runtimeDefinition->setIncrementalDateFields($incrementalDateFields);
    }
}
