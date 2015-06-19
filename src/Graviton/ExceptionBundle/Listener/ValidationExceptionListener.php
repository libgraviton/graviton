<?php
/**
 * Listener for validation exceptions
 */

namespace Graviton\ExceptionBundle\Listener;

use Graviton\ExceptionBundle\Exception\ValidationException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormError;

/**
 * Listener for validation exceptions
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ValidationExceptionListener extends RestExceptionListener
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
        if (($exception = $event->getException()) instanceof ValidationException) {
            $content = $this->getErrorMessages($exception->getErrors());
            // Set status code and content
            $response = new Response();
            $response
                ->setStatusCode(Response::HTTP_BAD_REQUEST)
                ->setContent(
                    $this->getSerializedContent($content)
                );

            $event->setResponse($response);
        }
    }

    /**
     * @param FormErrorIterator $errors errors
     *
     * @return array
     */
    private function getErrorMessages(FormErrorIterator $errors)
    {
        $content = [];
        foreach ($errors as $error) {
            if ($error instanceof FormErrorIterator) {
                $content = array_merge($content, $this->getErrorMessages($error));
            } elseif ($error instanceof FormError) {
                $cause = $error->getCause();
                if (!$cause) {
                    $path = 'unknkown';
                } else {
                    $path = $cause->getPropertyPath();
                }
                $content[] = [
                    'property_path' => $path,
                    'message' => $error->getMessage(),
                ];
            }
        }
        return $content;
    }
}
