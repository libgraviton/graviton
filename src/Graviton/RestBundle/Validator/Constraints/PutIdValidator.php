<?php

namespace Graviton\RestBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for PUT id handling.
 * This validator is only for the edge case that when the update ID in the request URL (GET)
 * differs from the one in the payload. this confused Doctrine ODM massively so we don't want
 * this to go through.
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @link     http://swisscom.com
 */
class PutIdValidator extends ConstraintValidator
{

    /**
     * Check if id is the same as the request id
     *
     * @param string     $value      Input value
     * @param Constraint $constraint Constraint instance
     *
     * @return void
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value != $constraint->getUpdateId()) {
            $this->context->buildViolation($constraint->message)
                          ->addViolation();
        }
    }
}
