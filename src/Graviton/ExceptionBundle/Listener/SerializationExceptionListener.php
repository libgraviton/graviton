<?php
/**
 * Listener for serialization exceptions
 */

namespace Graviton\ExceptionBundle\Listener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Graviton\ExceptionBundle\Exception\SerializationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Listener for serialization exceptions
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SerializationExceptionListener
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
        if (($exception = $event->getThrowable()) instanceof SerializationException) {
            $msg = [
                'message' => 'serialization problem: '.$exception->getMessage()
            ];

            $event->setResponse(
                new JsonResponse($msg, Response::HTTP_INTERNAL_SERVER_ERROR)
            );
        }
    }
}
