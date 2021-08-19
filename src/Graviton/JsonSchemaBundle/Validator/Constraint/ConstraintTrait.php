<?php
/**
 * trait for custom constraint classes
 */

namespace Graviton\JsonSchemaBundle\Validator\Constraint;

use JsonSchema\Entity\JsonPointer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
trait ConstraintTrait
{

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * sets the event dispatcher
     *
     * @param EventDispatcherInterface $dispatcher dispatcher
     *
     * @return void
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * checks the input
     *
     * @param mixed       $element           element
     * @param null        $schema            schema
     * @param JsonPointer $path              path
     * @param null        $properties        properties
     * @param null        $additionalProp    added props
     * @param null        $patternProperties pattern props
     * @param array       $appliedDefaults   applied defaults
     *
     * @return void
     */
    public function check(
        &$element,
        $schema = null,
        JsonPointer $path = null,
        $properties = null,
        $additionalProp = null,
        $patternProperties = null,
        $appliedDefaults = array()
    ) {
        $eventClass = $this->getEventClass();

        $event = new $eventClass($this->factory, $element, $schema, $path);
        $result = $this->dispatcher->dispatch($event, $event::NAME);

        $this->addErrors($result->getErrors());

        parent::check($element, $schema, $path, $properties, $additionalProp, $patternProperties, $appliedDefaults);
    }

    /**
     * Returns the name of the Event class for this event
     *
     * @return string event class name
     */
    abstract public function getEventClass();

    /**
     * Adds errors
     *
     * @param array $errors errors
     *
     * @return void
     */
    abstract public function addErrors(array $errors);
}
