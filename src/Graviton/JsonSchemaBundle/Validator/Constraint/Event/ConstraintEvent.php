<?php
/**
 * constraint event
 */

namespace Graviton\JsonSchemaBundle\Validator\Constraint\Event;

use JsonSchema\Constraints\Factory;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ConstraintEvent extends Event
{
    /**
     * @var string
     */
    const NAME = 'graviton.json_schema.constraint';

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var mixed
     */
    private $element;

    /**
     * @var mixed
     */
    private $schema;

    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * ConstraintEvent constructor.
     *
     * @param Factory $factory factory
     * @param mixed   $element element
     * @param mixed   $schema  schema
     * @param string  $path    path
     */
    public function __construct(Factory $factory, $element, $schema, $path)
    {
        $this->factory = $factory;
        $this->element = $element;
        $this->schema = $schema;
        $this->path = $path;
    }

    /**
     * @return Factory
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @return mixed
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * @return mixed
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * add an error message
     *
     * @param string $errorMessage message
     * @param string $propertyPath property path
     * @param string $constraint   constraint
     *
     * @return void
     */
    public function addError($errorMessage, $propertyPath = null, $constraint = '')
    {
        $this->errors[] = [
            'property' => (is_null($propertyPath) ? $this->getPath() : $propertyPath),
            'message' => $errorMessage,
            'constraint' => $constraint
        ];
    }
}
