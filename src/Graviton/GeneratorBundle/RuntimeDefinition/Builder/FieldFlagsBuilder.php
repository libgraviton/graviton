<?php
/**
 * FieldFlagsBuilder
 */

namespace Graviton\GeneratorBundle\RuntimeDefinition\Builder;

use Graviton\GeneratorBundle\RuntimeDefinition\RuntimeDefinitionBuilderAbstract;

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
     * @param RuntimeBuilderData $data data
     *
     * @return void
     */
    public function build(RuntimeBuilderData $data) : void
    {

        $baseSchema = $this->getSchemaBaseObject($data->definition, $data->schemaFile);
        $fields = $this->getAllFields($baseSchema);

        $recordOriginExceptionFields = [];
        $readOnlyFields = [];
        $incrementalDateFields = [];
        $extRefFields = [];

        foreach ($fields as $path => $field) {
            if (isset($field->{'x-recordOriginException'}) && $field->{'x-recordOriginException'} === true) {
                $recordOriginExceptionFields[] = $path;
            }

            if (isset($field->{'readOnly'}) && $field->{'readOnly'} === true) {
                $readOnlyFields[] = $path;
            }

            if (isset($field->{'x-incrementalDate'}) && $field->{'x-incrementalDate'} === true) {
                $incrementalDateFields[] = $path;
            }

            if (isset($field->{'format'}) && $field->{'format'} == 'extref') {
                $extRefFields[] = $path;
            }
        }

        $data->runtimeDefinition->setRecordOriginExceptionFields($recordOriginExceptionFields);
        $data->runtimeDefinition->setReadOnlyFields($readOnlyFields);
        $data->runtimeDefinition->setIncrementalDateFields($incrementalDateFields);
        $data->runtimeDefinition->setExtRefFields($extRefFields);
    }
}
