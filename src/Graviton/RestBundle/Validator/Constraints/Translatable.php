<?php
/**
 * Constraint for a strict boolean check (not accepting integers of any kind)
 */

namespace Graviton\RestBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for a strict boolean check (not accepting integers of any kind)
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
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
