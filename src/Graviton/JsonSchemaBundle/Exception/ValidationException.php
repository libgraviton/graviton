<?php
/**
 * ValidationException class file
 */

namespace Graviton\JsonSchemaBundle\Exception;

/**
 * ValidationException
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ValidationException extends \RuntimeException
{
    /**
     * @var ValidationExceptionError[]
     */
    protected $errors = array();

    /**
     * @param ValidationExceptionError[] $errors errors
     */
    public function __construct(array $errors)
    {
        $message = '';

        foreach ($errors as $error) {
            $message .= sprintf('* %s: %s', $error->getProperty(), $error->getMessage()).PHP_EOL;
            $this->addError($error);
        }

        parent::__construct(rtrim($message));
    }

    /**
     * Add an error
     *
     * @param ValidationExceptionError $error error
     *
     * @return void
     */
    public function addError(ValidationExceptionError $error)
    {
        $this->errors[] = $error;
    }

    /**
     * Returns all errors
     *
     * @return ValidationExceptionError[] errors
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
