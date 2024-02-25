<?php
/**
 * BodyChecker
 */

namespace Graviton\RestBundle\Service;

use Graviton\ExceptionBundle\Exception\MalformedInputException;
use Graviton\RestBundle\Model\DocumentModel;
use Graviton\RestBundle\Service\BodyChecks\BodyCheckerInterface;
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
     * @var BodyCheckerInterface[]
     */
    private array $bodyChecks = [];

    public function __construct()
    {

    }

    public function addBodyCheck(BodyCheckerInterface $bodyChecker)
    {
        $this->bodyChecks[] = $bodyChecker;
    }

    public function checkRequest(Request $request, DocumentModel $model, ?string $existingId) : void
    {
        $existingPayload = null;
        $existingDiff = null;
        if (!empty($existingId)) {
            try {
                $existingPayload = $model->getSerialised($existingId);
                $existingDiff = new JsonDiff(
                    json_decode($existingPayload),
                    json_decode((string)$request->getContent()),
                    JsonDiff::REARRANGE_ARRAYS
                );
            } catch (\Throwable $t) {
                throw new MalformedInputException('Unable to determine diff between input and current.', $t);
            }
        }

        foreach ($this->bodyChecks as $check) {
            $check->check($request, $model, $existingId, $existingPayload, $existingDiff);
        }
    }
}
