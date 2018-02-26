<?php
/**
 * Listener for no input exceptions
 */

namespace Graviton\ExceptionBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Graviton\ExceptionBundle\Exception\NoInputException;

/**
 * Listener for no input exceptions
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class NoInputExceptionListener extends RestExceptionListener
{
    /**
     * Handle the exception and send the right response
     *
     * @param GetResponseForExceptionEvent $event Event
     *
     * @return void
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (($exception = $event->getException()) instanceof NoInputException) {
            $msg = array("message" => "No input data");

            $response = $exception->getResponse()
                ->setStatusCode(Response::HTTP_BAD_REQUEST)
                ->setContent($this->getSerializedContent($msg));

            $event->setResponse($response);
        }
    }
}
