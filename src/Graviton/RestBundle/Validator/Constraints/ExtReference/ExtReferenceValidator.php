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
     * @throws \InvalidArgumentException
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ExtReference) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\ExtReference');
        }

        if ($value === null) {
            return;
        }

        if ($value instanceof ExtRef) {
            if (is_array($constraint->allowedCollections) &&
                !in_array('*', $constraint->allowedCollections, true) &&
                !in_array($value->getRef(), $constraint->allowedCollections, true)
            ) {
                $this->context->addViolation($constraint->notAllowedMessage, ['%url%' => $value]);
            }
        } else {
            $this->context->addViolation($constraint->invalidMessage, ['%url%' => $value]);
        }
    }
}
