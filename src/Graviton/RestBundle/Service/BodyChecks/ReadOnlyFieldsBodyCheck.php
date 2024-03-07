<?php
/**
 * ReadOnlyFieldsBodyCheck
 */

namespace Graviton\RestBundle\Service\BodyChecks;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
readonly class ReadOnlyFieldsBodyCheck extends BodyCheckerAbstract
{

    public function check(BodyCheckData $data): void
    {
        if (empty($data->jsonExisting)) {
            // no existing -> don't do anything
            return;
        }

        // ok, need to do checking!
        $runtimeDef = $data->model->getRuntimeDefinition();

        // nothing done!
        if (empty($runtimeDef->getReadOnlyFields()) || count($data->getAllModifiedFields()) < 1) {
            return;
        }

        // check modified fields
        $modifiedFields = $data->getAllModifiedFields();
        $readOnlyFields = $data->pathListToPatchFormat($runtimeDef->getReadOnlyFields());

        foreach ($modifiedFields as $modifiedField) {
            if (in_array($modifiedField, $readOnlyFields)) {
                throw new BodyCheckViolation(
                    sprintf(
                        'The fields "%s" are read-only in this service.',
                        implode(', ', $readOnlyFields)
                    ),
                    $modifiedField
                );
            }
        }
    }
}
