<?php
/**
 * Created by PhpStorm.
 * User: taachja1
 * Date: 13/04/16
 * Time: 10:16
 */

namespace Graviton\CoreBundle\Listener;

use Monolog\Logger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Graviton\JsonSchemaBundle\Exception\ValidationException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Graviton\RqlParser\Exception\SyntaxErrorException;
use Graviton\ExceptionBundle\Exception\SerializationException;

/**
 * Class JsonExceptionListener
 * @package Graviton\CoreBundle\Listener
 */
class JsonExceptionListener
{

    /**
     * @var Logger
     */
    private $logger;

    /**
     * set Logger
     *
     * @param Logger $logger logger
     *
     * @return void
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

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

        $data = $this->decorateKnownCases($exception);

        if (!is_array($data)) {
            $data = [
                'code' => $exception->getCode(),
                'exceptionClass' => get_class($exception),
                'message' => $exception->getMessage()
            ];

            if ($exception->getPrevious() instanceof \Exception) {
                $data['innerMessage'] = $exception->getPrevious()->getMessage();
            }
        }

        if ($this->logger instanceof Logger) {
            $this->logger->critical($exception);
        }

        $response = new JsonResponse($data);
        $event->setResponse($response);
    }

    /**
     * Here we can pick up known cases that can happen and render a more detailed error message for the client.
     * It may be cumbersome, but it's good to detail error messages then just to let general error messages
     * generate support issues and work for us.
     *
     * @param \Exception $exception exception
     *
     * @return array|null either a error message array or null if the general should be displayed
     */
    private function decorateKnownCases($exception)
    {
        if (
            $exception instanceof \ErrorException &&
            strpos($exception->getMessage(), 'Undefined index: $id') !== false
        ) {
            return [
                'code' => $exception->getCode(),
                'message' => 'An incomplete internal MongoDB ref has been discovered that can not be rendered. '.
                    'Did you pass a select() RQL statement and shaved off on the wrong level? Try to select a level '.
                    'higher.'
            ];
        } elseif (
            $exception instanceof SerializationException &&
            strpos($exception->getMessage(), 'Cannot serialize content class') !== false
        ) {
            $error = $exception->getMessage();
            $message =  strpos($error, 'not be found.') !== false ?
                substr($error, 0, strpos($error, 'not be found.')).'not be found.' : $error;
            preg_match('/\bwith id: (.*);.*?\bdocument\\\(.*)".*?\bidentifier "(.*)"/is', $message, $matches);
            if (array_key_exists(3, $matches)) {
                $sentence = 'Internal Database reference error as been discovered. '.
                    'The object id: "%s" has a reference to document: "%s" with id: "%s" that could not be found.';
                $message = sprintf($sentence, $matches[1], $matches[2], $matches[3]);
            }
            return [
                'code' => $exception->getCode(),
                'message' => $message
            ];
        }
    }
}
