<?php
/**
 * Listener for rql syntax error exceptions
 */

namespace Graviton\RestBundle\Listener;

use Graviton\RqlParser\Exception\SyntaxErrorException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * Listener for validation exceptions
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RqlSyntaxErrorListener
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
        if (($exception = $event->getThrowable()) instanceof SyntaxErrorException) {
            $msg = [
                'message' => sprintf('syntax error in rql: %s', $exception->getMessage())
            ];

            $event->setResponse(
                new JsonResponse($msg, Response::HTTP_BAD_REQUEST)
            );
        }
    }
}
