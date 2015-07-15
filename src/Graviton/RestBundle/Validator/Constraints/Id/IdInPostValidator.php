<?php
/**
 * Validator for a POST request
 */

namespace Graviton\RestBundle\Validator\Constraints\Id;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator that validates if its a POST request with an ID in the payload
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class IdInPostValidator extends ConstraintValidator
{

    /**
     * Checks if the ID is not in the payload of a POST request
     *
     * @param string     $value      Input value
     * @param Constraint $constraint Constraint instance
     *
     * @return void
     */
    public function validate($value, Constraint $constraint)
    {
        $HTTPVerb = $this->context->getRoot()->getConfig()->getMethod();
        $id = $this->context->getRoot()->getData()->getId();
        if ($HTTPVerb === 'POST' && isset($id)) {
                $this->context->addViolation($constraint->message);
        }
    }
}
