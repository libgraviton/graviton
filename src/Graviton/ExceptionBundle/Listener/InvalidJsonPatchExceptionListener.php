<?php
/**
 * Listener for invalid JSON Patch exceptions
 */

namespace Graviton\ExceptionBundle\Listener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Graviton\ExceptionBundle\Exception\InvalidJsonPatchException;

/**
 * Listener for invalid JSON Patch exceptions
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class InvalidJsonPatchExceptionListener
{
    /**
     * Handle the exception and send the right response
     *
     * @param ExceptionEvent $event Event
     *
     * @return void
     */
    public function onKernelException(ExceptionEvent $event)
    {
        if (($exception = $event->getThrowable()) instanceof InvalidJsonPatchException) {
            $msg = [
                'message' => sprintf('Invalid JSON patch request: %s', $exception->getMessage())
            ];

            $event->setResponse(
                new JsonResponse($msg, Response::HTTP_BAD_REQUEST)
            );
        }
    }
}
