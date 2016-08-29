<?php
/**
 * Field constraint that validates if the posted date is greater than the one saved in the database.
 */

namespace Graviton\SchemaBundle\Constraint;

use Graviton\JsonSchemaBundle\Validator\Constraint\Event\ConstraintEventFormat;
use JsonSchema\Rfc3339;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class IncrementalDateFieldConstraint
{
    /**
     * Constructor
     *
     * @param ConstraintUtils $utils Utils
     */
    public function __construct(ConstraintUtils $utils)
    {
        $this->utils = $utils;
    }

    /**
     * Checks the readOnly fields and sets error in event if needed
     *
     * @param ConstraintEventFormat $event event class
     *
     * @return void
     */
    public function checkIncrementalDate(ConstraintEventFormat $event)
    {
        $schema = $event->getSchema();

        if (!isset($schema->{'x-constraints'}) ||
            (is_array($schema->{'x-constraints'}) && !in_array('incrementalDate', $schema->{'x-constraints'}))
        ) {
            return;
        }

        // get the current record
        $currentRecord = $this->utils->getCurrentEntity();

        if (is_null($currentRecord)) {
            return;
        }

        $data = $event->getElement();
        $path = $this->utils->getNormalizedPathFromPointer($event->getPath());

        // get the current value in database
        $accessor = PropertyAccess::createPropertyAccessor();

        if (!$accessor->isReadable($currentRecord, $path)) {
            // value is not saved in db..
            return;
        }

        $storedValue = $accessor->getValue($currentRecord, $path);
        $storedDate = Rfc3339::createFromString($storedValue);
        $userDate = Rfc3339::createFromString($data);

        if ($userDate <= $storedDate) {
            $event->addError(
                sprintf('The date must be greater than the saved date %s', $storedValue)
            );
        }
    }
}
