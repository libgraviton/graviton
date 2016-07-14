<?php
/**
 * Schema constraint that validates if readOnly: true fields are manipulated and rejects changes on those.
 */

namespace Graviton\SchemaBundle\Constraint;

use Graviton\JsonSchemaBundle\Validator\Constraint\Event\ConstraintEventSchema;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ReadOnlyFieldConstraint
{

    /**
     * @var array
     */
    private $fieldMap;

    /**
     * ReadOnlyFieldConstraint constructor.
     *
     * @param ConstraintUtils $utils             Utils
     * @param array           $readOnlyFieldsMap field map from compiler pass
     */
    public function __construct(ConstraintUtils $utils, array $readOnlyFieldsMap)
    {
        $this->utils = $utils;
        $this->fieldMap = $readOnlyFieldsMap;
    }

    /**
     * Checks the readOnly fields and sets error in event if needed
     *
     * @param ConstraintEventSchema $event event class
     *
     * @return void
     */
    public function checkReadOnlyFields(ConstraintEventSchema $event)
    {
        $schema = $event->getSchema();
        $data = $event->getElement();

        if (!isset($schema->{'x-documentClass'}) || !isset($data->id)) {
            return;
        }

        $documentClass = $schema->{'x-documentClass'};

        if (!isset($this->fieldMap[$documentClass])) {
            return;
        }

        $readOnlyFields = $this->fieldMap[$documentClass];

        // get the current record
        $currentRecord = $this->utils->getCurrentEntity();

        if (is_null($currentRecord)) {
            return;
        }

        // compare fields in both objects
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($readOnlyFields as $fieldName) {
            $storedValue = null;
            if ($accessor->isReadable($currentRecord, $fieldName)) {
                $storedValue = $accessor->getValue($currentRecord, $fieldName);
            }

            if (is_object($storedValue)) {
                // skip objects as a whole, we will test their readOnly properties instead
                continue;
            }

            $setValue = null;
            if ($accessor->isReadable($data, $fieldName)) {
                $setValue = $accessor->getValue($data, $fieldName);
            }

            if ($storedValue != $setValue) {
                $event->addError(
                    sprintf('The value %s is read only.', json_encode($accessor->getValue($currentRecord, $fieldName))),
                    $fieldName
                );
            }
        }
    }
}
