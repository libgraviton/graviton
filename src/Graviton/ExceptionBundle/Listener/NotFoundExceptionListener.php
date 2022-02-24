<?php
/**
 * '404' listener
 */

namespace Graviton\ExceptionBundle\Listener;

use Laminas\Diactoros\Response\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Graviton\ExceptionBundle\Exception\NotFoundException;

/**
 * Listener for not found exceptions
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class NotFoundExceptionListener
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
        if (($exception = $event->getThrowable()) instanceof NotFoundException) {
            $msg = ["message" => $exception->getMessage()];

            $event->setResponse(
                new JsonResponse($msg, Response::HTTP_NOT_FOUND)
            );
        }
    }
}
