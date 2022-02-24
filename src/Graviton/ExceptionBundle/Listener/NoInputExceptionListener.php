<?php
/**
 * Listener for no input exceptions
 */

namespace Graviton\ExceptionBundle\Listener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Graviton\ExceptionBundle\Exception\NoInputException;

/**
 * Listener for no input exceptions
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class NoInputExceptionListener
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
        if (($exception = $event->getThrowable()) instanceof NoInputException) {
            $msg = ["message" => "No input data"];

            $event->setResponse(
                new JsonResponse($msg, Response::HTTP_BAD_REQUEST)
            );
        }
    }
}
