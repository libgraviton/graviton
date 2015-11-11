<?php
/**
 * ValidStatus constraint
 */

namespace Graviton\RabbitMqBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for ValidStatus
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ValidStatus extends Constraint
{
    /**
     * Error message
     *
     * @var string
     */
    public $message = '"%string%" is not a valid status string (must be one of %status%).';

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function validatedBy()
    {
        return 'graviton.rabbitmq.validator.validstatus';
    }
}
