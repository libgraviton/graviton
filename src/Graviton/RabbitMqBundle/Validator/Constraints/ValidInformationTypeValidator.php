<?php
/**
 * ValidInformationType validator
 */

namespace Graviton\RabbitMqBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for ValidInformationType
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ValidInformationTypeValidator extends ConstraintValidator
{

    /**
     * allowed type strings
     *
     * @var array allowed type string
     */
    private $allowedTypes = [];

    /**
     * adds a new allowed status
     *
     * @param string $type type
     *
     * @return void
     */
    public function addType($type)
    {
        $this->allowedTypes[] = $type;
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
        if (!in_array($value, $this->allowedTypes)) {
            $this->context->addViolation(
                $constraint->message,
                ['%string%' => $value, '%type%' => implode(', ', $this->allowedTypes)]
            );
        }
    }
}
