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
        $message['message'] = $exception->getMessage();
        $message['code'] = $exception->getCode();
        $message['file'] = new \stdClass;
        $message['file']->path = $exception->getFile();
        $message['file']->line = $exception->getLine();
        $message['trace'] = array_map(
                function($line) { return $this->prepareTraceLine($line); },
                $exception->getTrace()
        );

        $headers = array('Content-Type' => 'application/json');

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

        $trace['args'] = $line['args'];

        return $trace;
    }
}
