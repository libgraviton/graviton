<?php
namespace Graviton\ExceptionBundle\Listener;

use Graviton\ExceptionBundle\Exception\ValidationException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;

/**
 * Listener for validation exceptions
 *
 * @category GravitonExceptionBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class ValidationExceptionListener extends RestExceptionListener
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
        if (($exception = $event->getException()) instanceof ValidationException) {
            $response = $exception->getResponse();

            // Set status code and content
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->setContent(
                $this->getSerializedContent($exception->getViolations())
            );

            $event->setResponse($response);
        }
    }
}
