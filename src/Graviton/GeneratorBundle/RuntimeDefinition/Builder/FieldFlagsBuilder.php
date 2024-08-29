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

        // take top level fields also from json definiton again
        $jsonFields = $data->definition->getFields();
        foreach ($jsonFields as $jsonField) {
            // only take simple fields!
            if (str_contains($jsonField->getName(), '.')) {
                continue;
            }

            $def = $jsonField->getDefAsArray();
            if (isset($def['readOnly']) && $def['readOnly'] === true) {
                $readOnlyFields[] = $jsonField->getName();
            }
            if (isset($def['recordOriginException']) && $def['recordOriginException'] === true) {
                $recordOriginExceptionFields[] = $jsonField->getName();

                // also subfields?
                $allowSubFields = ($jsonField->getType() == 'object' || $def['isClassType'] == true);
                if ($allowSubFields) {
                    $recordOriginExceptionFields[] = $jsonField->getName().'.*';
                }
            }
        }

        $data->runtimeDefinition->setRecordOriginExceptionFields(
            array_unique($recordOriginExceptionFields)
        );
        $data->runtimeDefinition->setReadOnlyFields(
            array_unique($readOnlyFields)
        );
        $data->runtimeDefinition->setIncrementalDateFields(
            array_unique($incrementalDateFields)
        );
        $data->runtimeDefinition->setExtRefFields(
            array_unique($extRefFields)
        );
    }
}
