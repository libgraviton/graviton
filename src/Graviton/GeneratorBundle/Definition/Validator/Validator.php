<?php
/**
 * Validator class file
 */

namespace Graviton\GeneratorBundle\Definition\Validator;

use HadesArchitect\JsonSchemaBundle\Exception\ViolationException;
use HadesArchitect\JsonSchemaBundle\Validator\ValidatorServiceInterface;

/**
 * JSON definition validation
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class Validator implements ValidatorInterface
{
    /**
     * @var \stdClass JSON schema
     */
    private $schema;
    /**
     * @var ValidatorServiceInterface Validator
     */
    private $validator;

    /**
     * Constructor
     *
     * @param ValidatorServiceInterface $validator Validator
     * @param \stdClass                 $schema    JSON schema
     */
    public function __construct(ValidatorServiceInterface $validator, \stdClass $schema)
    {
        $this->validator = $validator;
        $this->schema = $schema;
    }

    /**
     * Validate raw JSON definition
     *
     * @param string $json JSON definition
     * @return void
     * @throws InvalidJsonException If JSON is not valid
     * @throws ViolationException   If definition is not valid
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

        $this->validator->check($json, $this->schema);
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
