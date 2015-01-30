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
class TranslatableValidator extends ConstraintValidator
{

    /**
     * One of those keys need to exist in the structure in order to be
     * a valid Translatable input
     *
     * @var array
     */
    private $minimalKeys = array(
        'en',
        'de',
        'fr'
    );

    /**
     * See if it's a valid Translatable
     *
     * @param string     $value      Input value
     * @param Constraint $constraint Constraint instance
     *
     * @return void
     */
    public function validate($value, Constraint $constraint)
    {
        $allOk = true;

        if (!is_array($value)) {
            $allOk = false;
        } else {
            // minimal keys present?
            $intersection = array_intersect(array_keys($value), $this->minimalKeys);

            if (count($intersection) < 1) {
                $allOk = false;
            }
        }

        if (!$allOk) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
