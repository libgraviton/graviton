<?php
/**
 * Listener for rql syntax error exceptions
 */

namespace Graviton\ExceptionBundle\Listener;

use Graviton\RqlParser\Exception\SyntaxErrorException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\Response;

/**
 * Listener for validation exceptions
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RqlSyntaxErrorListener extends RestExceptionListener
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
        if (($exception = $event->getException()) instanceof SyntaxErrorException) {
            // Set status code and content
            $response = new Response();
            $response
                ->setStatusCode(Response::HTTP_BAD_REQUEST)
                ->setContent(
                    $this->getSerializedContent(
                        [
                            'message' => sprintf('syntax error in rql: %s', $exception->getMessage())
                        ]
                    )
                );
            $event->setResponse($response);
        }
    }
}
