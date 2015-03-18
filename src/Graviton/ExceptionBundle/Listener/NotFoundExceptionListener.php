<?php
/**
 * '404' listener
 */

namespace Graviton\ExceptionBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Graviton\ExceptionBundle\Exception\NotFoundException;

/**
 * Listener for not found exceptions
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class NotFoundExceptionListener extends RestExceptionListener
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
        if (($exception = $event->getException()) instanceof NotFoundException) {
            $msg = array("message" => $exception->getMessage());
            // Set status code and content
            $response = $exception->getResponse()
                ->setStatusCode(Response::HTTP_NOT_FOUND)
                ->setContent($this->getSerializedContent($msg));

            $event->setResponse($response);
        }
    }
}
