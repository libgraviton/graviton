<?php
namespace Graviton\RestBundle\Validation;

use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Validator class for json inputs
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class JsonInput
{
    /**
     * Validator
     *
     * @var Symfony\Component\Validator\Validator
     */
    private $validator;

    /**
     * Constructor
     *
     * @param Symfony\Component\Validator\Validator $validator Validator
     *
     * @return void
     */
    public function __construct($validator)
    {
        $this->validator = $validator;
    }

    /**
     * Validate the json input values and check for non existing values
     *
     * @param String                                  $input Json input string
     * @param Graviton\RestBundle\Model\DocumentModel $model Model
     *
     * @return Symfony\Component\Validator\ConstraintViolationList $violations Constraint violation list
     */
    public function validate($input, $model)
    {
        // get all fields of this document
        $manager = $model->getRepository()->getDocumentManager();
        $entityFields = $manager->getClassMetadata($model->getEntityClass())
            ->getFieldNames();

        // get validation info
        $classMetadata = $this->validator->getMetadataFor($model->getEntityClass());
        $constrainedProps = $classMetadata->getConstrainedProperties();

        $input = json_decode($input, true);

        $violations = new ConstraintViolationList();

        // Validate input values
        foreach ($constrainedProps as $prop) {
            $metadata = $classMetadata->getPropertyMetadata($prop);
            $constraints = $metadata[0]->constraints;

            // if the value is set, validate...
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
        }

        // Check for non existing attributes
        foreach (array_keys($input) as $key) {
            if (!in_array($key, $entityFields)) {
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
            if ($constraint instanceof \Symfony\Component\Validator\Constraints\NotBlank) {
                $required = true;
            }
        }

        return $required;
    }

    /**
     * Create a new violation list with the given violations
     *
     * @param String                                              $prop             Property
     * @param Symfony\Component\Validator\ConstraintViolationList $validationResult Violation list
     *
     * @return \Symfony\Component\Validator\ConstraintViolationList $violations Violations
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
