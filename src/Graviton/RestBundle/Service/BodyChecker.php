<?php
/**
 * BodyChecker
 */

namespace Graviton\RestBundle\Service;

use Graviton\ExceptionBundle\Exception\MalformedInputException;
use Graviton\RestBundle\Model\DocumentModel;
use Graviton\RestBundle\Service\BodyChecks\BodyCheckData;
use Graviton\RestBundle\Service\BodyChecks\BodyCheckerAbstract;
use Psr\Http\Message\ServerRequestInterface;
use Rs\Json\Pointer;
use Swaggest\JsonDiff\JsonDiff;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
readonly class BodyChecker
{

    /**
     * @param \SplStack $bodyChecks body checks
     */
    public function __construct(
        public \SplStack $bodyChecks = new \SplStack(),
    ) {
    }

    /**
     * add a bodycheck
     *
     * @param BodyCheckerAbstract $bodyChecker body check
     *
     * @return void
     */
    public function addBodyCheck(BodyCheckerAbstract $bodyChecker)
    {
        $this->bodyChecks->push($bodyChecker);
    }

    /**
     * check the request
     *
     * @param ServerRequestInterface $request    req
     * @param Response               $response   resp
     * @param DocumentModel          $model      model
     * @param string|null            $existingId existing id
     *
     * @return ServerRequestInterface
     *
     * @throws Pointer\InvalidJsonException
     * @throws Pointer\NonWalkableJsonException
     * @throws \Swaggest\JsonDiff\Exception
     */
    public function checkRequest(
        ServerRequestInterface $request,
        Response $response,
        DocumentModel $model,
        ?string $existingId
    ) : ServerRequestInterface {
        $existingPayload = null;
        $existingDiff = null;
        $existingPointer = null;

        if (!empty($existingId) && $model->recordExists($existingId)) {
            try {
                $existingPayload = $model->getSerialised($existingId);
                $existingDiff = new JsonDiff(
                    json_decode($existingPayload),
                    json_decode((string) $request->getBody()),
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

        try {
            foreach ($this->bodyChecks as $check) {
                $check->check($data);
            }
        } catch (\Throwable $t) {
            throw $t;
        } finally {
            // has modifiers?
            foreach ($data->userPayloadModifier as $modifier) {
                $request = $modifier($request);
            }
            foreach ($data->responseModifier as $modifier) {
                $modifier($response);
            }
        }

        // return potentially modified request!
        return $request;
    }
}
