<?php
namespace Graviton\ExceptionBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Graviton\ExceptionBundle\Exception\SerializationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Listener for serialization exceptions
 *
 * @category GravitonExceptionBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class SerializationExceptionListener extends RestExceptionListener
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
        if (($exception = $event->getException()) instanceof SerializationException) {
            $response = $exception->getResponse();
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);

            $event->setResponse($response);
        }
    }
}
