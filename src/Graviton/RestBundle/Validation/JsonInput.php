<?php
namespace Graviton\RestBundle\Validation;

use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\LegacyValidator as Validator;
use Graviton\RestBundle\Model\DocumentModel;

/**
 * Validator class for json inputs
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 *
 * @todo refactor as to not use LegacyValidator that was introduced by the 2.5 bump
 */
class JsonInput
{
    /**
     * Validator
     *
     * @var Validator
     */
    private $validator;

    /**
     * Constructor
     *
     * @param Validator $validator Validator
     *
     * @return void
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Validate the json input and check for non existing values
     *
     * @param string        $input Json input string
     * @param DocumentModel $model Model
     *
     * @return ConstraintViolationList $violations Constraint violation list
     */
    public function validate($input, DocumentModel $model)
    {
        // Get the entity manager
        $em = $model->getRepository()->getDocumentManager();

        // Get all fields of this document / entity
        $fields = $em->getClassMetadata($model->getEntityClass())->getFieldNames();

        // Get class metadata
        $metadata = $this->validator->getMetadataFor($model->getEntityClass());

        // Get properties with constraints
        $props = $metadata->getConstrainedProperties();

        // Decode the json from request
        $input = json_decode($input, true);

        // Create a new ConstraintViolationList
        $violations = new ConstraintViolationList();

        foreach ($props as $key => $prop) {
            // check if
            $propertyMetadata = $metadata->getPropertyMetadata($prop);
            $constraints = $propertyMetadata[0]->constraints;

            // ToDo: Check for nested documents...
            // If the property is a nested document, the constraint of this prop will be "Valid"
            // http://symfony.com/doc/2.3/reference/constraints/Valid.html
            // In this case, load metadata for this class an check it.
            // constraints = $this->validate(input['subobject'], subobjectclass or whatever...);

            // Check every single prop
            if (isset($input[$prop])) {
                $val = $input[$prop];
                $validationResult = $this->validator->validateValue($val, $constraints);
                $violations->addAll($this->createNewViolationList($prop, $validationResult));
            } else {
                // if it's not set but required, validate with empty value
                if ($this->isRequired($constraints)) {
                    $validationResult = $this->validator->validateValue(null, $constraints);
                    $violations->addAll($this->createNewViolationList($prop, $validationResult));
                }
            }

            // Check for non existing properties.
            // Don't know if this is necessary, remove it if not...
            if (is_array($input)) {
                $diff = array_diff(array_keys($input), $fields);
                foreach ($diff as $field) {
                    $violation = new ConstraintViolation(
                        'Attribute does not exist!',
                        'Attribute does not exist!',
                        array(),
                        $key,
                        $key,
                        $key
                    );

                    $violations->add($violation);
                }
            }
        }

        return $violations;
    }

    /**
     * Checks if a value is required
     *
     * @param array $constraints constraints for this value
     *
     * @return boolean $required true/false
     */
    private function isRequired($constraints)
    {
        $required = false;

        foreach ($constraints as $constraint) {
            if ($constraint instanceof NotBlank) {
                $required = true;
            }
        }

        return $required;
    }

    /**
     * Create a new violation list with the given violations
     *
     * @param string                  $prop             Property
     * @param ConstraintViolationList $validationResult Violation list
     *
     * @return ConstraintViolationList $violations Violations
     */
    private function createNewViolationList($prop, $validationResult)
    {
        $violations = new ConstraintViolationList();

        foreach ($validationResult as $violation) {
            $newViolation = new ConstraintViolation(
                $violation->getMessage(),
                $violation->getMessageTemplate(),
                $violation->getParameters(),
                $violation->getRoot(),
                $prop,
                $violation->getInvalidValue(),
                $violation->getPlural(),
                $violation->getCode()
            );

            $violations->add($newViolation);
        }

        return $violations;
    }
}
