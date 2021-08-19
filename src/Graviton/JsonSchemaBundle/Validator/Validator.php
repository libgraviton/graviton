<?php
/**
 * Validator class file
 */

namespace Graviton\JsonSchemaBundle\Validator;

use Graviton\JsonSchemaBundle\Exception\ValidationExceptionError;

/**
 * JSON definition validation
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class Validator implements ValidatorInterface
{
    /**
     * @var string JSON schema location
     */
    private $schema;
    /**
     * @var Validator Validator
     */
    private $validator;

    /**
     * Constructor
     *
     * @param Validator $validator Validator
     * @param string    $schema    JSON schema
     */
    public function __construct($validator, $schema)
    {
        $this->validator = $validator;
        $this->schema = $schema;
    }

    /**
     * Validate raw JSON definition
     *
     * @param string $json JSON definition
     * @return ValidationExceptionError[]
     * @throws InvalidJsonException If JSON is not valid
     */
    public function validateJsonDefinition($json)
    {
        $json = json_decode($json);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidJsonException(sprintf('Malformed JSON: %s', $this->getJsonLastErrorMessage()));
        }
        if (!is_object($json)) {
            throw new InvalidJsonException('JSON value must be an object');
        }

        return $this->validate($json, $this->schema);
    }

    /**
     * validate a json structure with a schema
     *
     * @param object $json   the json
     * @param object $schema the schema
     *
     * @return ValidationExceptionError[] errors
     */
    public function validate($json, $schema)
    {
        $this->validator->reset();
        if (is_string($schema)) {
            $this->validator->validate($json, (object) ['$ref' => $schema]);
        } else {
            $this->validator->validate($json, $schema);
        }

        if ($this->validator->isValid()) {
            return [];
        }

        return $this->getErrors();
    }

    /**
     * Wraps the array exception in our own class
     *
     * @return ValidationExceptionError[]
     */
    public function getErrors()
    {
        $errors = [];
        foreach ($this->validator->getErrors() as $error) {
            $errors[] = new ValidationExceptionError($error);
        }
        return $errors;
    }

    /**
     * Get JSON decode last error message
     *
     * @return string
     */
    private function getJsonLastErrorMessage()
    {
        if (function_exists('json_last_error_msg')) {
            return json_last_error_msg();
        }

        $errorNo = json_last_error();
        $errorMap = [
            JSON_ERROR_DEPTH            => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH   => 'Underflow or modes mismatch',
            JSON_ERROR_CTRL_CHAR        => 'Unexpected control character found',
            JSON_ERROR_SYNTAX           => 'Syntax error, malformed JSON',
            JSON_ERROR_UTF8             => 'Malformed UTF-8 characters, possibly incorrectly encoded',
        ];
        return isset($errorMap[$errorNo]) ? $errorMap[$errorNo]: 'Unknown error';
    }
}
