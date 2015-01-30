<?php

namespace Graviton\RestBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for a strict boolean check (not accepting integers of any kind)
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @link     http://swisscom.com
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
