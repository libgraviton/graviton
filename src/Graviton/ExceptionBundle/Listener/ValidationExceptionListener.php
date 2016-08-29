<?php
/**
 * Listener for validation exceptions
 */

namespace Graviton\ExceptionBundle\Listener;

use Graviton\JsonSchemaBundle\Exception\ValidationException;
use Graviton\JsonSchemaBundle\Exception\ValidationExceptionError;
use Graviton\SchemaBundle\Constraint\ConstraintUtils;
use JsonSchema\Entity\JsonPointer;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;

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
     * @var ConstraintUtils
     */
    private $constraintUtils;

    /**
     * set constraint utils
     *
     * @param ConstraintUtils $utils utils
     *
     * @return void
     */
    public function setConstraintUtils(ConstraintUtils $utils)
    {
        $this->constraintUtils = $utils;
    }

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
     * @param ValidationExceptionError[] $errors errors
     *
     * @return array
     */
    private function getErrorMessages(array $errors)
    {
        $content = [];
        foreach ($errors as $error) {
            $property = $error->getProperty();
            if ($property instanceof JsonPointer && $this->constraintUtils instanceof ConstraintUtils) {
                $property = $this->constraintUtils->getNormalizedPathFromPointer($property);
            }
            $content[] = [
                'propertyPath' => $property,
                'message' => $error->getMessage(),
            ];
        }
        return $content;
    }
}
