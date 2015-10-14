<?php
/**
 * ValidStatus validator
 */

namespace Graviton\RabbitMqBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for ValidStatus
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ValidStatusValidator extends ConstraintValidator
{

    /**
     * allowed status strings
     *
     * @var array allowed status string
     */
    private $allowedStatus = [];

    /**
     * adds a new allowed status
     *
     * @param string $status status
     *
     * @return void
     */
    public function addStatus($status)
    {
        $this->allowedStatus[] = $status;
    }

    /**
     * validates the input
     *
     * @param mixed      $value      value
     * @param Constraint $constraint constraint
     *
     * @return void
     */
    public function validate($value, Constraint $constraint)
    {
        if (!in_array($value, $this->allowedStatus)) {
            $this->context->addViolation(
                $constraint->message,
                ['%string%' => $value, '%status%' => implode(', ', $this->allowedStatus)]
            );
        }
    }
}
