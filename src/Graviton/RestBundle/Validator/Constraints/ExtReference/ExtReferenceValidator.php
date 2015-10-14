<?php
/**
 * ExtReferenceValidator class file
 */

namespace Graviton\RestBundle\Validator\Constraints\ExtReference;

use Graviton\DocumentBundle\Entity\ExtReference as ExtRef;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validator for the extref type
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ExtReferenceValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param mixed      $value      The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     * @return void
     * @throws UnexpectedTypeException
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ExtReference) {
            throw new UnexpectedTypeException($constraint, ExtReference::class);
        }

        if ($value === null) {
            return;
        }

        if (!$value instanceof ExtRef) {
            throw new UnexpectedTypeException($value, ExtRef::class);
        }

        if (!empty($constraint->collections) &&
            !in_array('*', $constraint->collections, true) &&
            !in_array($value->getRef(), $constraint->collections, true)
        ) {
            $this->context->addViolation($constraint->message, ['%collection%' => $value->getRef()]);
        }
    }
}
