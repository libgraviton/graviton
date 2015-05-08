<?php
/**
 * Validator class for json inputs
 */

namespace Graviton\RestBundle\Validator;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Graviton\RestBundle\Validator\Constraints\PostId;
use Graviton\RestBundle\Validator\Constraints\PutId;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Mapping\ClassMetadata as SFClassMetaData;
use Symfony\Component\Validator\Validator\LegacyValidator as Validator;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 *
 * @todo     refactor as to not use LegacyValidator that was introduced by the 2.5 bump
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
     * Document Manager
     *
     * @var DocumentManager
     */
    private $em;

    /**
     * Request
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * Validation list
     *
     * @var ConstraintViolationList
     */
    private $violations;

    /**
     * Constructor
     *
     * @param Validator $validator Validator
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
        $this->violations = new ConstraintViolationList();
    }

    /**
     * Get metadata/constraints of the given class an validate
     *
     * @param array  $input         Json input (decoded)
     * @param string $documentClass Classname (document)
     *
     * @return \Symfony\Component\Validator\ConstraintViolationList $violations Violations
     */
    public function validate($input, $documentClass)
    {
        $this->checkDocument($input, $documentClass);

        return $this->violations;
    }

    /**
     * Check the given document
     *
     * @param array  $input         Json input (decoded)
     * @param string $documentClass Classname (document)
     * @param string $path          Path to the value
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\Validator\ConstraintViolationList $violations Violations
     */
    public function checkDocument(array $input, $documentClass, $path = '')
    {
        if (!$this->em) {
            throw new \Exception("No document manager set");
        }

        // Get metadata for this document
        $documentMetadata = $this->em->getClassMetadata($documentClass);
        $fields = $documentMetadata->getFieldNames();

        // Get validation metadata for this document
        $validationMetadata = $this->validator->getMetadataFor($documentClass);

        // method dependent checks
        if ($this->request->getMethod() == 'POST') {
            // id is not allowed in POST payload - except it is required!
            // some services *need* to set the id in some rare circumstances - like language resource!
            $idMetadata = $validationMetadata->getPropertyMetadata('id');

            $isIdRequired = false;
            if (is_array($idMetadata)) {
                foreach ($idMetadata as $metadata) {
                    foreach ($metadata->getConstraints() as $constraint) {
                        if ($constraint instanceof NotBlank) {
                            $isIdRequired = true;
                        }
                    }
                }
            }

            if (!$isIdRequired) {
                $validationMetadata->addPropertyConstraint('id', new PostId());
            }
        }

        if ($this->request->getMethod() == 'PUT') {
            // id is not allowed in POST payload
            $putIdConstraint = new PutId();
            $putIdConstraint->setUpdateId($this->getRequest()->get('id'));
            $validationMetadata->addPropertyConstraint('id', $putIdConstraint);
        }

        foreach ($fields as $key => $property) {
            if (!$path) {
                $violations = $this->checkProperty($property, $input, $documentMetadata, $validationMetadata);
            } else {
                $violations = $this->checkProperty(
                    $path . "." . $property,
                    $input,
                    $documentMetadata,
                    $validationMetadata
                );
            }
        }

        return $violations;
    }

    /**
     * Check a single property
     *
     * @param string                                             $path               Path to property
     * @param array                                              $input              Json input (decoded)
     * @param \Doctrine\ODM\MongoDB\Mapping\ClassMetadata        $documentMetadata   Doctrine metadata
     * @param \Symfony\Component\Validator\Mapping\ClassMetadata $validationMetadata Validator metadata
     *
     * @return \Symfony\Component\Validator\ConstraintViolationList $violations Violations
     */
    public function checkProperty(
        $path,
        array $input,
        ClassMetadata $documentMetadata,
        SFClassMetaData $validationMetadata
    ) {
        // empty violation list
        $violations = new ConstraintViolationList();

        // get the last part of the path... this is the property
        $parts = explode('.', $path);
        $property = end($parts);

        // get validation constraints for this property
        $propertyMetadata = $validationMetadata->getPropertyMetadata($property);

        $constraints = array();
        if (isset($propertyMetadata[0])) {
            $constraints = $propertyMetadata[0]->constraints;
        }

        // is the property set? If not, check for required
        if (isset($input[$property])) {
            // Is the given property an association?
            if ($documentMetadata->hasAssociation($property)) {
                $violations = $this->checkAssociation($path, $input, $documentMetadata);
            } else {
                $violations = $this->checkConstraints($path, $input[$property], $constraints);
            }
        } else {
            if ($this->isRequired($constraints)) {
                $violations = $this->checkConstraints($path, null, $constraints);
            }
        }

        $this->violations->addAll($violations);

        return $violations;
    }

    /**
     * Check an association (embedded documents)
     *
     * @param string        $path             Path to property
     * @param array         $input            Json input
     * @param ClassMetadata $documentMetadata Document metadata
     *
     * @return \Symfony\Component\Validator\ConstraintViolationList $violations Violations
     */
    private function checkAssociation($path, array $input, ClassMetadata $documentMetadata)
    {
        $parts = explode('.', $path);
        $property = end($parts);

        // Check association type
        if ($documentMetadata->isSingleValuedAssociation($property)) {
            $className = $documentMetadata->getAssociationTargetClass($property);
            $violations = $this->checkDocument($input[$property], $className, $path);
        } else {
            $violations = new ConstraintViolationList();
            $className = $documentMetadata->getAssociationTargetClass($property);
            foreach ($input[$property] as $key => $value) {
                $violations->addAll($this->checkDocument($value, $className, $path . "[" . $key . "]"));
            }
        }

        return $violations;
    }

    /**
     * Get violations for a given value
     *
     * @param string $path        Path to the value
     * @param mixed  $value       Value to check
     * @param array  $constraints Constraints
     *
     * @return \Symfony\Component\Validator\ConstraintViolationList $violations Violations
     */
    private function checkConstraints($path, $value, array $constraints)
    {
        $validationResult = $this->validator->validateValue($value, $constraints);
        $violations = $this->createNewViolationList($path, $validationResult);

        return $violations;
    }

    /**
     * Set the document manager
     *
     * @param \Doctrine\ODM\MongoDB\DocumentManager $em Doctrine document manager
     *
     * @return JsonInput self
     */
    public function setDocumentManager(DocumentManager $em)
    {
        $this->em = $em;

        return $this;
    }

    /**
     * Get the document manager
     *
     * @return \Doctrine\ODM\MongoDB\DocumentManager
     */
    public function getDocumentManager()
    {
        return $this->em;
    }

    /**
     * Gets the request
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Sets the request
     *
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return void
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * Checks if a value is required
     *
     * @param array $constraints constraints for this value
     *
     * @return boolean $required true/false
     */
    private function isRequired(array $constraints)
    {
        $required = false;

        foreach ($constraints as $constraint) {
            if ($constraint instanceof NotBlank || $constraint instanceof NotNull) {
                $required = true;
            }
        }

        return $required;
    }

    /**
     * Create a new violation list with the given violations
     *
     * @param string                                               $prop             Property
     * @param \Symfony\Component\Validator\ConstraintViolationList $validationResult Violation list
     *
     * @return \Symfony\Component\Validator\ConstraintViolationList $violations Violations
     */
    private function createNewViolationList($prop, ConstraintViolationList $validationResult)
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
