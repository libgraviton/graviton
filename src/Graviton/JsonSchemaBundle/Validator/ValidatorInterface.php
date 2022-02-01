<?php
/**
 * ValidatorInterface class file
 */

namespace Graviton\JsonSchemaBundle\Validator;

/**
 * JSON definition validation interface
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
interface ValidatorInterface
{
    /**
     * Validate raw JSON definition
     *
     * @param string $json JSON definition
     * @return Error[]
     * @throws InvalidJsonException If JSON is not valid
     */
    public function validateJsonDefinition($json);
}
