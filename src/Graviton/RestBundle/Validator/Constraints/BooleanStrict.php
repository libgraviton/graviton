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
class BooleanStrict extends Constraint
{

    /**
     * Error message
     *
     * @var string
     */
    public $message = 'The value "%string%" is not a valid boolean.';
}
