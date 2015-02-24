<?php
/**
 * Validator for a strict boolean check (not accepting integers of any kind)
 */

namespace Graviton\RestBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for a strict boolean check (not accepting integers of any kind)
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class BooleanStrictValidator extends ConstraintValidator
{

    /**
     * Check strictly for boolean
     *
     * @param string     $value      Input value
     * @param Constraint $constraint Constraint instance
     *
     * @return void
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value !== true && $value !== false) {
            $this->context->buildViolation($constraint->message)
                          ->setParameter('%string%', $value)
                          ->addViolation();
        }
    }
}
