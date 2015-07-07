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
    private $dm;

    /**
     * Creates a new ReadOnlyValidator instance
     *
     * @param DocumentManager $dm Document manager
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
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
        $submittedData = $this->context->getRoot()->getData();

        // if the structure is nested it can't access the id via getObject()
        $recordClass = get_class($submittedData);
        $recordId = $this->context->getRoot()->getData()->getId();

        $record = $this->dm->find($recordClass, $recordId);

        $path = explode('.', $this->context->getPropertyPath());

        $storedValue = $record->{'get' . $path[1]}()->{'get' . $path[2]}();

        if ($record && $value !== $storedValue) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%string%', $this->context->getPropertyPath())
                ->addViolation();
        }
    }
}
