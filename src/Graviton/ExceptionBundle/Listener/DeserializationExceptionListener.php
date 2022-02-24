<?php
/**
 * Listener for deserialization exceptions
 */

namespace Graviton\ExceptionBundle\Listener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Graviton\ExceptionBundle\Exception\DeserializationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Listener for deserialization exceptions
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class DeserializationExceptionListener
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
        if (($exception = $event->getThrowable()) instanceof DeserializationException) {
            // hnmm.. no way to find out which property (name) failed??
            $msg = ['message' => $exception->getPrevious()->getMessage()];

            $event->setResponse(
                new JsonResponse($msg, Response::HTTP_BAD_REQUEST)
            );
        }
    }
}
