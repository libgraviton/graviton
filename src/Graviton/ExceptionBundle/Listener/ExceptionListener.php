<?php

namespace Graviton\ExceptionBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $response = new JsonResponse();

        $message = array();
        $message['class'] = get_class($exception);
        $message['message'] = $exception->getMessage();
        $message['code'] = $exception->getCode();
        $message['file'] = new \stdClass;
        $message['file']->path = $exception->getFile();
        $message['file']->line = $exception->getLine();
        $message['trace'] = array_map(
            function ($line) {
                return $this->prepareTraceLine($line);
            },
            $exception->getTrace()
        );

        $headers = array(
            'Content-Type' => 'application/vnd.graviton.exception+json',
            'Link' => '</core/schema/graviton.exception>; type="application/vnd.graviton.schema+json"; rel="schema"',
        );

        if ($exception instanceof HttpExceptionInterface) {
                $response->setStatusCode($exception->getStatusCode());
                // add headers from exception if any exist
                $headers = array_merge(
                    $exception->getHeaders(),
                    $headers
                );
        } else {
                // @todo use JsonResponse::HTTP_INTERNAL_SERVER_ERROR when it becomes available
                $response->setStatusCode(500);
        }
        $response->headers->replace($headers);
        $response->setData($message);

        $event->setResponse($response);
    }

    /**
     * prepare a line of backtrace into a semi human readable json
     */
    private function prepareTraceLine($line)
    {
        $trace = array();
        if (array_key_exists('file', $line)) {
            $trace['file'] = new \stdClass;
            $trace['file']->path = $line['file'];
            $trace['file']->line = $line['line'];
        }

        if (array_key_exists('class', $line)) {
            $trace['call'] = $line['class'].$line['type'].$line['function'];
        } else {
            $trace['call'] = $line['function'];
        }

        $trace['args'] = array_map(
            function ($arg) {
                return $this->walkArg($arg);
            },
            $line['args']
        );

        return $trace;
    }

    /**
     * somehow mangle the the arguments of a trace into a somewhat jsonifiable form
     *
     * This is rather hacky (the whole class actually is). Since we won't be using
     * the exception walking part on prod anyway it can stay as is for now.
     *
     * I would like to refactor this into something more nice later on but there
     * are some more important things that should be up and running first for
     * that to make sense.
     */
    private function walkArg($arg)
    {
        if (is_array($arg)) {
            return array_map(
                function ($array) {
                    return $this->walkArg($array);
                },
                $arg
            );
        } elseif (is_object($arg)) {
            return 'instanceof '.get_class($arg);
        } else {
            return $arg;
        }
    }
}
