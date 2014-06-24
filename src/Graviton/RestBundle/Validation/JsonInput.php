<?php
namespace Graviton\RestBundle\Validation;

use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Validate a json input and add validation messages
 * if attributes don't exist 
 * 
 *
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
		$entityClass = $model->getEntityClass();
		$record = new $entityClass();
		
		$classMetadata = $validator->getMetadataFor($record);
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
			if (!in_array($key, $constrainedProps)) {
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