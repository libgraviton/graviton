<?php
/**
 * Schema constraint that validates the rules of recordOrigin (and possible exceptions)
 */

namespace Graviton\SchemaBundle\Constraint;

use Graviton\JsonSchemaBundle\Validator\Constraint\Event\ConstraintEventSchema;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class RecordOriginConstraint
{

    /**
     * @var string
     */
    private $recordOriginField;

    /**
     * @var array
     */
    private $recordOriginBlacklist;

    /**
     * @var array
     */
    private $exceptionFieldMap;

    /**
     * RecordOriginConstraint constructor.
     *
     * @param ConstraintUtils $utils                 Utils
     * @param string          $recordOriginField     name of the recordOrigin field
     * @param array           $recordOriginBlacklist list of recordOrigin values that cannot be modified
     * @param array           $exceptionFieldMap     field map from compiler pass with excluded fields
     */
    public function __construct(
        ConstraintUtils $utils,
        $recordOriginField,
        array $recordOriginBlacklist,
        array $exceptionFieldMap
    ) {
        $this->utils = $utils;
        $this->recordOriginField = $recordOriginField;
        $this->recordOriginBlacklist = $recordOriginBlacklist;
        $this->exceptionFieldMap = $exceptionFieldMap;
    }

    /**
     * Checks the recordOrigin rules and sets error in event if needed
     *
     * @param ConstraintEventSchema $event event class
     *
     * @return void
     */
    public function checkRecordOrigin(ConstraintEventSchema $event)
    {
        $currentRecord = $this->utils->getCurrentEntity();
        $data = $event->getElement();

        // if no recordorigin set on saved record; we let it through
        if (is_null($currentRecord) || !isset($currentRecord->{$this->recordOriginField})) {
            // we have no current record.. but make sure user doesn't want to send the banned recordOrigin
            if (isset($data->{$this->recordOriginField}) &&
                !is_null($data->{$this->recordOriginField}) &&
                in_array($data->{$this->recordOriginField}, $this->recordOriginBlacklist)
            ) {
                $event->addError(
                    sprintf(
                        'Creating documents with the %s field having a value of %s is not permitted.',
                        $this->recordOriginField,
                        implode(', ', $this->recordOriginBlacklist)
                    ),
                    $this->recordOriginField
                );
                return;
            }

            return;
        }

        $recordOrigin = $currentRecord->{$this->recordOriginField};

        // not in the blacklist? can also go through..
        if (!in_array($recordOrigin, $this->recordOriginBlacklist)) {
            return;
        }

        // ok, user is trying to modify an object with blacklist recordorigin.. let's check fields
        $schema = $event->getSchema();
        $isAllowed = true;

        if (!isset($schema->{'x-documentClass'})) {
            // this should never happen but we need to check. if schema has no information to *check* our rules, we
            // MUST deny it in that case..
            $event->addError(
                'Internal error, not enough schema information to validate recordOrigin rules.',
                $this->recordOriginField
            );
            return;
        }

        $documentClass = $schema->{'x-documentClass'};

        if (!isset($this->exceptionFieldMap[$documentClass])) {
            // if he wants to edit on blacklist, but we have no exceptions, also deny..
            $isAllowed = false;
        } else {
            // so to check our exceptions, we remove it from both documents (the stored and the clients) and compare
            $exceptions = $this->exceptionFieldMap[$documentClass];

            $accessor = PropertyAccess::createPropertyAccessorBuilder()
                ->enableMagicCall()
                ->getPropertyAccessor();

            $storedObject = clone $currentRecord;
            $userObject = clone $data;

            foreach ($exceptions as $fieldName) {
                if ($accessor->isWritable($storedObject, $fieldName)) {
                    $accessor->setValue($storedObject, $fieldName, null);
                } else {
                    $this->addProperties($fieldName, $storedObject);
                }
                if ($accessor->isWritable($userObject, $fieldName)) {
                    $accessor->setValue($userObject, $fieldName, null);
                } else {
                    $this->addProperties($fieldName, $userObject);
                }
            }

            // so now all unimportant fields were set to null on both - they should match if rest is untouched ;-)
            if ($userObject != $storedObject) {
                $isAllowed = false;
            }
        }

        if (!$isAllowed) {
            $forbiddenFields = array_keys((array) $this->utils->getCurrentSchema()->properties);
            if (isset($this->exceptionFieldMap[$documentClass]) && is_array($this->exceptionFieldMap[$documentClass])) {
                $forbiddenFields = array_diff($forbiddenFields, $this->exceptionFieldMap[$documentClass]);
            }
            $event->addError(
                sprintf(
                    'Prohibited modification attempt on record with %s of %s. '.
                    'BTW, You are also not allowed to write in (%s)',
                    $this->recordOriginField,
                    implode(', ', $this->recordOriginBlacklist),
                    implode(', ', $forbiddenFields)
                ),
                $this->recordOriginField
            );
        }

        return;
    }

    /**
     * if the user provides properties that are in the exception list but not on the currently saved
     * object, we try here to synthetically add them to our representation. and yes, this won't support
     * exclusions in an array structure for the moment, but that is also not needed for now.
     *
     * @param string $expression the expression
     * @param object $obj        the object
     *
     * @return object the modified object
     */
    private function addProperties($expression, $obj)
    {
        $val = &$obj;
        $parts = explode('.', $expression);
        $numParts = count($parts);

        if ($numParts == 1) {
            $val->{$parts[0]} = null;
        } else {
            $iteration = 1;
            foreach ($parts as $part) {
                if ($iteration < $numParts) {
                    if (!isset($val->{$part}) || !is_object($val->{$part})) {
                        $val->{$part} = new \stdClass();
                    }
                    $val = &$val->{$part};
                } else {
                    $val->{$part} = null;
                }
                $iteration++;
            }
        }

        return $val;
    }
}
