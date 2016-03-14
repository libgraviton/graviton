<?php
/**
 * ExtReferenceValidator class file
 */

namespace Graviton\RestBundle\Validator\Constraints\ExtReference;

use Graviton\DocumentBundle\Entity\ExtReference as ExtRef;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Validator for the extref type
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ExtReferenceValidator extends ConstraintValidator
{

    /** @var DocumentManager $documentManager */
    private $documentManager;

    /** @var Boolean */
    private $validateId;

    /**
     * ExtReferenceValidator constructor.
     * @param DocumentManager $documentManager DB manager
     * @param boolean         $validateId      Validate ID against DB or not.
     */
    public function __construct(DocumentManager $documentManager = null, $validateId = null)
    {
        $this->documentManager = $documentManager;
        $this->validateId = $validateId;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed      $value      The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     * @return void
     * @throws UnexpectedTypeException
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ExtReference) {
            throw new UnexpectedTypeException($constraint, ExtReference::class);
        }

        if ($value === null) {
            return;
        }

        if (!$value instanceof ExtRef) {
            throw new UnexpectedTypeException($value, ExtRef::class);
        }

        if (!empty($constraint->collections) &&
            !in_array('*', $constraint->collections, true) &&
            !in_array($value->getRef(), $constraint->collections, true)
        ) {
            $this->context->addViolation($constraint->message, ['%collection%' => $value->getRef()]);
            return;
        }

        // Does ID exists in DB.
        if ($this->validateId && !in_array($value->getRef(),['App', 'TestCaseReadOnly'])) {
            $db = $this->documentManager->getConnection()->selectDatabase('db');
            $collection = $db->selectCollection($value->getRef());
            $document = $collection->findOne(['_id' => $value->getId()]);
            if (!$document) {
                $msgArg = ['%collection%' => $value->getRef().':invalid:'.$value->getId()];
                $this->context->addViolation($constraint->message, $msgArg);
            }
        }

    }
}
