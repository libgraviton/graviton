<?php
/**
 * Validator for read only check
 */

namespace Graviton\RestBundle\Validator\Constraints\ReadOnly;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for read only
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ReadOnlyValidator extends ConstraintValidator
{

    /**
     * Checks read only
     *
     * @param string     $value      Input value
     * @param Constraint $constraint Constraint instance
     *
     * @return void
     */
    public function validate($value, Constraint $constraint)
    {
      //  $test = get_class($value);
        if (true) {
            $id = $this->context->getObject();
            $t = get_class($id);
            $c = str_replace("\\",".", $t);
            $a = new $t();
            $q = $this->get($c);
          //  $b = $a->find(102);
           // $id = "102";
//            $model = $this->context->getMetadata()->getPropertyValue();
//            if (!($record = $this->getModel()->find($id))) {
//                $e = new NotFoundException("Entry with id " . $id . " not found!");
//                $e->setResponse($response);
//                throw $e;
//            }
//            $this->context->buildViolation($constraint->message)
//                          ->setParameter('%string%', $this->context->getPropertyPath())
//                          ->addViolation();
        }
    }
}
