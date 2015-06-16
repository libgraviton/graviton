<?php
/**
 * Validation exception class
 */

namespace Graviton\ExceptionBundle\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormError;

/**
 * Validation exception class
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
final class ValidationException extends RestException
{
    /**
     * Violations
     *
     * @var FormErrorIterator
     */
    private $errors;

    /**
     * Constructor
     *
     * @param string            $message Error message
     * @param FormErrorIterator $errors  Errors from form
     */
    public function __construct($message, FormErrorIterator $errors)
    {
        if (empty($message)) {
            $message = 'Validation failed';
        }
        $this->errors = $errors;
        parent::__construct(Response::HTTP_BAD_REQUEST, $message);
    }

    /**
     * @return FormErrorIterator
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
