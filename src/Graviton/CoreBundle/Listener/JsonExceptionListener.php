<?php
/**
 * json exception listener
 */

namespace Graviton\CoreBundle\Listener;

use Graviton\RestBundle\Exception\SerializationException;
use Graviton\RestBundle\Service\BodyChecks\BodyCheckViolation;
use Graviton\RqlParser\Exception\SyntaxErrorException;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
readonly class JsonExceptionListener
{

    /**
     * @param LoggerInterface $logger logger
     */
    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * Should not handle Validation Exceptions and only service exceptions
     *
     * @param ExceptionEvent $event Sf Event
     *
     * @return void
     */
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        $this->logger->critical($exception, ['exception' => $exception]);

        // Should return a error 400 bad request
        if ($exception instanceof ValidationFailed
         || $exception instanceof BodyCheckViolation
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

            if ($exception instanceof \Error) {
                $data['message'] = 'php internal error, details redacted. check logs.';
            }

            if ($exception->getPrevious() instanceof \Exception) {
                $data['innerMessage'] = $exception->getPrevious()->getMessage();
            }
        }

        $event->setResponse(new JsonResponse($data));
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
    private function decorateKnownCases($exception) : ?array
    {
        if (
            $exception instanceof \ErrorException &&
            str_contains($exception->getMessage(), 'Undefined index: $id')
        ) {
            return [
                'code' => $exception->getCode(),
                'message' => 'An incomplete internal MongoDB ref has been discovered that can not be rendered. '.
                    'Did you pass a select() RQL statement and shaved off on the wrong level? Try to select a level '.
                    'higher.'
            ];
        } elseif (
            $exception instanceof SerializationException &&
            str_contains($exception->getMessage(), 'Cannot serialize content class')
        ) {
            $error = $exception->getMessage();
            $message =  str_contains($error, 'not be found.') ?
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

        return null;
    }
}
