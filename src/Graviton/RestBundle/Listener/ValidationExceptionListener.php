<?php
/**
 * Listener for validation exceptions
 */

namespace Graviton\RestBundle\Listener;

use Graviton\RestBundle\Service\BodyChecks\BodyCheckViolation;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidBody;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
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
class ValidationExceptionListener
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
        // body check violation?
        if ($event->getThrowable() instanceof BodyCheckViolation) {
            $event->setResponse(
                new JsonResponse(
                    [
                        [
                            'propertyPath' => $event->getThrowable()->propertyPath,
                            'message' => $event->getThrowable()->getMessage()
                        ]
                    ],
                    Response::HTTP_BAD_REQUEST
                )
            );
            return;
        }

        if (($exception = $event->getThrowable()) instanceof ValidationFailed) {
            $event->setResponse(
                new JsonResponse(
                    $this->getErrorMessages($exception),
                    Response::HTTP_BAD_REQUEST
                )
            );
        }
    }

    /**
     * get messages
     *
     * @param ValidationFailed $exception exception
     *
     * @return array messages
     */
    private function getErrorMessages(ValidationFailed $exception) : array
    {
        $content = [];

        $content[] = $this->getMessageFromThrowable($exception);

        $prev = $exception->getPrevious();
        while (!is_null($prev)) {
            $content[] = $this->getMessageFromThrowable($prev);
            $prev = $prev->getPrevious();
        }

        return $content;
    }

    /**
     * gets the message for 1
     *
     * @param \Throwable $exception exception
     *
     * @return array message
     */
    private function getMessageFromThrowable(\Throwable $exception) : array
    {
        $propertyPath = '';

        if ($exception instanceof InvalidBody) {
            $propertyPath = '.body';
        }

        if (is_callable([$exception, 'dataBreadCrumb'])) {
            $breadCrumb = $exception->dataBreadCrumb();
            $propertyPath = implode('.', $breadCrumb->buildChain());
            if (empty($propertyPath)) {
                $propertyPath = '.';
            }
        }

        return [
            'propertyPath' => $propertyPath,
            'message' => $exception->getMessage()
        ];
    }
}
