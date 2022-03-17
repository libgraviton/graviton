<?php
/**
 * custom format constraint
 */

namespace Graviton\JsonSchemaBundle\Validator\Constraint;

use JsonSchema\Constraints\SchemaConstraint as BaseSchemaConstraint;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SchemaConstraint extends BaseSchemaConstraint
{

    use ConstraintTrait;

    /**
     * class of the event
     *
     * @var string
     */
    private $eventClass = 'Graviton\JsonSchemaBundle\Validator\Constraint\Event\ConstraintEventSchema';

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
