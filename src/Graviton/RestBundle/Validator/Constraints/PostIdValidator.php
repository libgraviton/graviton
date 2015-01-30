<?php

namespace Graviton\RestBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for id POST presence.
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @link     http://swisscom.com
 */
class PostIdValidator extends ConstraintValidator
{

    /**
     * See if value is not null..
     *
     * @param string     $value      Input value
     * @param Constraint $constraint Constraint instance
     *
     * @return void
     */
    public function validate($value, Constraint $constraint)
    {
        if (!is_null($value)) {
            $this->context->buildViolation($constraint->message)
                          ->addViolation();
        }
    }
}
