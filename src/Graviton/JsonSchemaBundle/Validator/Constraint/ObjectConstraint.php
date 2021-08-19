<?php
/**
 * custom object constraint
 */

namespace Graviton\JsonSchemaBundle\Validator\Constraint;

use JsonSchema\Constraints\ObjectConstraint as BaseObjectConstraint;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ObjectConstraint extends BaseObjectConstraint
{

    use ConstraintTrait;

    /**
     * class of the event
     *
     * @var string
     */
    private $eventClass = 'Graviton\JsonSchemaBundle\Validator\Constraint\Event\ConstraintEventObject';

    /**
     * Returns the name of the Event class for this event
     *
     * @return string event class name
     */
    public function getEventClass()
    {
        return $this->eventClass;
    }
}
