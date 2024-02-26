<?php
/**
 * RecordOriginBodyCheck
 */

namespace Graviton\RestBundle\Service\BodyChecks;

use Rs\Json\Pointer;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
readonly class RecordOriginBodyCheck extends BodyCheckerAbstract
{

    public function __construct(private string $recordOriginField, private array $recordOriginBlacklist)
    {
    }

    public function check(BodyCheckData $data): void
    {
        if (empty($data->jsonExisting)) {
            // it is not allowed to create records with the blacklist origins!
            $payloadPointer = new Pointer((string) $data->request->getContent());
            try {
                $origin = $payloadPointer->get('/recordOrigin');

                $list = array_map('strtolower', $this->recordOriginBlacklist);

                if (!empty($origin) && in_array(strtolower(trim($origin)), $list)) {
                    throw new BodyCheckViolation(
                        sprintf(
                            'It is not allowed to create records with recordOrigin values "%s"',
                            implode(', ', $this->recordOriginBlacklist)
                        ),
                        'recordOrigin'
                    );
                }
            } catch (\Throwable $t) {
                // does not exist; return also
            }

            return;
        }

        $pointer = $data->jsonExisting;
        try {
            $existingRecordOrigin = $pointer->get('/'.$this->recordOriginField);

            // empty -> finish
            if (empty($existingRecordOrigin)) {
                return;
            }

            if (!in_array($existingRecordOrigin, $this->recordOriginBlacklist)) {
                return;
            }
        } catch (\Throwable $t) {
            // nothing -> finish
            return;
        }

        // ok, need to do checking!
        $runtimeDef = $data->model->getRuntimeDefinition();

        // no exception fields but we have modifications! deny!
        if (empty($runtimeDef->getRecordOriginExceptionFields()) && $data->jsonDiff->getModifiedCnt() > 0) {
            throw new BodyCheckViolation(
                'Service does not allow for any data modification',
                'recordOrigin'
            );
        }

        // check modified fields
        $modifiedFields = $data->getAllModifiedFields();
        $allowedFields = $data->pathListToPatchFormat($runtimeDef->getRecordOriginExceptionFields());

        if (!$data->isListIncludedInSublist($allowedFields, $modifiedFields)) {
            throw new BodyCheckViolation(
                sprintf(
                    'Only the fields "%s" are allowed to be modified in this service if recordOrigin are in "%s"',
                    implode(', ', $allowedFields),
                    implode(', ', $this->recordOriginBlacklist)
                ),
                'recordOrigin'
            );
        }
    }
}
