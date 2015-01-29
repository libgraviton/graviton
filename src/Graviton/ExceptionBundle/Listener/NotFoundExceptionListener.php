<?php
namespace Graviton\ExceptionBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Graviton\ExceptionBundle\Exception\NotFoundException;

/**
 * Listener for not found exceptions
 *
 * @category GravitonExceptionBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
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
