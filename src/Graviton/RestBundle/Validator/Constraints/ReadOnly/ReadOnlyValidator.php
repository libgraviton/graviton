<?php
/**
 * Validator for read only
 */

namespace Graviton\RestBundle\Validator\Constraints\ReadOnly;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Validator for read only
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ReadOnlyValidator extends ConstraintValidator
{
    private $em;


    public function __construct(DocumentManager $em){
        $this->em = $em;
    }
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
        $recordId = $this->context->getObject()->getId();
        $recordClass = get_class($this->context->getObject());

        if ($this->em->find($recordClass, $recordId)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%string%', $this->context->getPropertyPath())
                ->addViolation();
        }
    }
}
