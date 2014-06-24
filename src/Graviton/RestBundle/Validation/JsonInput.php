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
     * Validate the json input values and check for non existing values
     *
     * @param String                                  $input     Json input string
     * @param Graviton\RestBundle\Model\DocumentModel $model     Model
     * @param \Symfony\Component\Validator\Validator  $validator validator class
     *
     * @return Symfony\Component\Validator\ConstraintViolationList $violations Constraint violation list
     */
    public static function validate($input, $model, $validator)
    {
        // get all fields of this document
        $dm = $model->getRepository()->getDocumentManager();
        $entityFields = $dm->getClassMetadata($model->getEntityClass())
            ->getFieldNames();

        // get validation info
        $classMetadata = $validator->getMetadataFor($model->getEntityClass());
        $constrainedProps = $classMetadata->getConstrainedProperties();

        $input = json_decode($input);

        $violations = new ConstraintViolationList();

        // Validate input values
        foreach ($constrainedProps as $prop) {
            $metadata = $classMetadata->getPropertyMetadata($prop);
            $validationResult = $validator->validateValue($input->$prop, $metadata[0]->constraints);
            $violations->addAll($validationResult);
        }

        // Check for non existing attributes
        foreach ($input as $key => $value) {
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
}
