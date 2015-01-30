<?php
namespace Graviton\RestBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for a strict boolean check (not accepting integers of any kind)
 *
 * @category GravitonRestBundle
 * @package  Graviton
 * @link     http://swisscom.com
 */
class Translatable extends Constraint
{

    /**
     * Error message
     *
     * @var string
     */
    public $message = 'Not a valid Translatable. A valid Translatable has the form {"en": "message"}';
}
