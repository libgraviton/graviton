<?php
namespace Graviton\ExceptionBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Graviton\ExceptionBundle\Exception\NoInputException;

/**
 * Listener for deserialization exceptions
 *
 * @category GravitonExceptionBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
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
                ->setStatusCode(Response::HTTP_BAD_REQUEST);

            $response->setContent(
                $this->getSerializedContent($msg)
            );

            $event->setResponse($response);
        }
    }
}
