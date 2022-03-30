<?php
/**
 * custom factory to inject the event dispatcher into our constraints
 */

namespace Graviton\JsonSchemaBundle\Validator\Constraint;

use JsonSchema\Constraints\BaseConstraint;
use JsonSchema\Constraints\Factory as BaseFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class Factory extends BaseFactory
{

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher = null;

    /**
     * set EventDispatcher
     *
     * @param EventDispatcherInterface $dispatcher dispatcher
     *
     * @return void
     */
    public function setEventDispatcher($dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Create a constraint instance for the given constraint name.
     *
     * @param string $constraintName constraint name
     *
     * @throws InvalidArgumentException if is not possible create the constraint instance.
     *
     * @return BaseConstraint instance
     */
    public function createInstanceFor($constraintName)
    {
        $instance = parent::createInstanceFor($constraintName);

        if (!is_null($this->dispatcher) && is_callable([$instance, 'setEventDispatcher'])) {
            $instance->setEventDispatcher($this->dispatcher);
        }

        return $instance;
    }
}
