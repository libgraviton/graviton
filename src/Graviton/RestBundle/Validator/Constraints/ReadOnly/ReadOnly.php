<?php
/**
 * Constraint for a read only
 */

namespace Graviton\RestBundle\Validator\Constraints\ReadOnly;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for read only
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ReadOnly extends Constraint
{

    /**
     * Error message
     *
     * @var string
     */
    public $message = 'The value "%string%" is read only.';
}
