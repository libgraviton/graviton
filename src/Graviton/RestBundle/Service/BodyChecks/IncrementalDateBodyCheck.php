<?php
/**
 * RecordOriginBodyCheck
 */

namespace Graviton\RestBundle\Service\BodyChecks;

use JsonSchema\Rfc3339;
use Rs\Json\Pointer;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
readonly class IncrementalDateBodyCheck extends BodyCheckerAbstract
{

    public function check(BodyCheckData $data): void
    {
        if (empty($data->jsonExisting)) {
            // no existing -> no need to check!
            return;
        }

        // nothing?
        $incDateFields = $data->pathListToPatchFormat(
            $data->model->getRuntimeDefinition()->getIncrementalDateFields()
        );

        if (empty($incDateFields)) {
            return;
        }

        $payload = new Pointer((string) $data->request->getContent());

        foreach ($incDateFields as $fieldPath) {
            $this->compareTwoFields(
                $fieldPath,
                $payload,
                $data->jsonExisting
            );
        }

    }

    private function compareTwoFields(string $fieldPath, Pointer $payload, Pointer $existing)
    {
        $existingDate = null;
        try {
            $existingDate = $existing->get($fieldPath);
        } catch (\Throwable $t) {
            // nope;
        }

        $newDate = null;
        try {
            $newDate = $payload->get($fieldPath);
        } catch (\Throwable $t) {
            // nope;
        }

        if (empty($existingDate)) {
            // no existing, all ok!
            return;
        }

        if (empty($newDate)) {
            throw new BodyCheckViolation(
                'Field must be specified with a datetime value.',
                $fieldPath
            );
        }

        $storedDate = Rfc3339::createFromString($existingDate);
        $userDate = Rfc3339::createFromString($newDate);

        if ($userDate <= $storedDate) {
            throw new BodyCheckViolation(
                sprintf('The date must be greater than the saved date %s', $existingDate),
                $fieldPath
            );
        }
    }
}
