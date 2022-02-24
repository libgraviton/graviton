<?php
/**
 * Listener for RestException exceptions
 */

namespace Graviton\ExceptionBundle\Listener;

use Graviton\ExceptionBundle\Exception\RestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RestExceptionListener
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
        if (($exception = $event->getThrowable()) instanceof RestException) {
            $innerMessage = $exception->getMessage();
            if ($exception->getPrevious() instanceof \Throwable) {
                $innerMessage .= ' - '.$exception->getPrevious()->getMessage();
            }

            $msg = [
                'type' => $exception::class,
                'message' => $innerMessage
            ];

            $event->setResponse(
                new JsonResponse($msg, $exception->getStatusCode())
            );
        }
    }
}
