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
        if (empty($runtimeDef->getReadOnlyFields())) {
            return;
        }

        // deleted and modified count, not added!
        $changedFields = array_unique(
            $data->jsonDiff->getModifiedPaths() +
            $data->jsonDiff->getRemovedPaths()
        );

        // check modified fields
        $readOnlyFields = $data->pathListToPatchFormat($runtimeDef->getReadOnlyFields());

        foreach ($changedFields as $modifiedField) {
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
