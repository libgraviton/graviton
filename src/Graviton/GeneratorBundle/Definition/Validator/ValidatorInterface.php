<?php
/**
 * ValidatorInterface class file
 */

namespace Graviton\GeneratorBundle\Definition\Validator;

use HadesArchitect\JsonSchemaBundle\Exception\ViolationException;

/**
 * JSON definition validation interface
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
interface ValidatorInterface
{
    /**
     * Validate raw JSON definition
     *
     * @param string $json JSON definition
     * @return void
     * @throws InvalidJsonException If JSON is not valid
     * @throws ViolationException   If definition is not valid
     */
    public function validateJsonDefinition($json);
}
