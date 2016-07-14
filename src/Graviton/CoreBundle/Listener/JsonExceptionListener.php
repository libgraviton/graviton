<?php
/**
 * Created by PhpStorm.
 * User: taachja1
 * Date: 13/04/16
 * Time: 10:16
 */

namespace Graviton\CoreBundle\Listener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Graviton\JsonSchemaBundle\Exception\ValidationException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Xiag\Rql\Parser\Exception\SyntaxErrorException;

/**
 * Class JsonExceptionListener
 * @package Graviton\CoreBundle\Listener
 */
class JsonExceptionListener
{
    /**
     * Should not handle Validation Exceptions and only service exceptions
     *
     * @param GetResponseForExceptionEvent $event Sf Event
     *
     * @return void
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        // Should return a error 400 bad request
        if ($exception instanceof ValidationException
         || $exception instanceof SyntaxErrorException) {
            return;
        }

        // Some Exceptions have status code and if 400 it should be handled by them
        if (method_exists($exception, 'getStatusCode')
            && (400 == (int)$exception->getStatusCode())) {
            return;
        }

        $data = [
            'code' => $exception->getCode(),
            'message' => $exception->getMessage()
        ];

        $response = new JsonResponse($data);
        $event->setResponse($response);
    }
}
