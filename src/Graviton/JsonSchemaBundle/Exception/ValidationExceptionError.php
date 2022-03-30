<?php
/**
 * ValidationExceptionError class file
 */

namespace Graviton\JsonSchemaBundle\Exception;

/**
 * ValidationExceptionError
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ValidationExceptionError
{

    /**
     * @var string
     */
    private $property;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $constraint;

    /**
     * @param array $error errpr
     */
    public function __construct(array $error)
    {
        if (isset($error['property'])) {
            $this->property = $error['property'];
        }
        if (isset($error['message'])) {
            $this->message = $error['message'];
        }
        if (isset($error['constraint'])) {
            $this->constraint = $error['constraint'];
        }
    }

    /**
     * get property path
     *
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * get constraint name
     *
     * @return string
     */
    public function getConstraint()
    {
        return $this->constraint;
    }
}
