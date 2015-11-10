<?php
/**
 * Constraint for a boolean check. It accept also null, because the value does not have to be set (optional)
 */

namespace Graviton\RestBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for a boolean check. It accept also null, because the value does not have to be set (optional)
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class OptionalBoolean extends Constraint
{
    /**
     * Error message
     *
     * @var string
     */
    public $message = 'The value "%string%" is not null or a valid boolean.';
}
