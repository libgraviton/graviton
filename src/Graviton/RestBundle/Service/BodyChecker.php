<?php
/**
 * BodyChecker
 */

namespace Graviton\RestBundle\Service;

use Graviton\ExceptionBundle\Exception\MalformedInputException;
use Graviton\RestBundle\Model\DocumentModel;
use Graviton\RestBundle\Service\BodyChecks\BodyCheckData;
use Graviton\RestBundle\Service\BodyChecks\BodyCheckerAbstract;
use Rs\Json\Pointer;
use Swaggest\JsonDiff\JsonDiff;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
class BodyChecker
{

    /**
     * @var BodyCheckerAbstract[]
     */
    private array $bodyChecks = [];

    public function __construct()
    {

    }

    public function addBodyCheck(BodyCheckerAbstract $bodyChecker)
    {
        $this->bodyChecks[] = $bodyChecker;
    }

    public function checkRequest(Request $request, DocumentModel $model, ?string $existingId) : void
    {
        $existingPayload = null;
        $existingDiff = null;
        $existingPointer = null;
        if (!empty($existingId)) {
            try {
                $existingPayload = $model->getSerialised($existingId);
                $existingDiff = new JsonDiff(
                    json_decode($existingPayload),
                    json_decode((string)$request->getContent()),
                    JsonDiff::REARRANGE_ARRAYS
                );
                $existingPointer = new Pointer($existingPayload);
            } catch (\Throwable $t) {
                throw new MalformedInputException('Unable to determine diff between input and current.', $t);
            }
        }

        $data = new BodyCheckData(
            $request,
            $model,
            $existingId,
            $existingPayload,
            $existingPointer,
            $existingDiff
        );

        foreach ($this->bodyChecks as $check) {
            $check->check($data);
        }
    }
}
