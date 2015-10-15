<?php
/**
 * ValidInformationType constraint
 */

namespace Graviton\RabbitMqBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for ValidInformationType
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ValidInformationType extends Constraint
{
    /**
     * Error message
     *
     * @var string
     */
    public $message = '"%string%" is not a valid information type (must be one of %type%).';

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function validatedBy()
    {
        return 'graviton.rabbitmq.validator.validinformationtype';
    }
}
