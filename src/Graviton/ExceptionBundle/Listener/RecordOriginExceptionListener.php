<?php
/**
 * Listener for recordOriginModified exceptions
 */

namespace Graviton\ExceptionBundle\Listener;

use Graviton\ExceptionBundle\Exception\RecordOriginModifiedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * Listener for recordOriginModified exceptions
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RecordOriginExceptionListener extends RestExceptionListener
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
        if (($exception = $event->getThrowable()) instanceof RecordOriginModifiedException) {
            $content = array(
                "propertyPath" => "recordOrigin",
                "message"      => $exception->getMessage(),
            );

            // Set status code and content
            $response = new Response();
            $response
                ->setStatusCode($exception->getStatusCode())
                ->setContent(
                    $this->getSerializedContent($content)
                );

            $event->setResponse($response);
        }
    }
}
