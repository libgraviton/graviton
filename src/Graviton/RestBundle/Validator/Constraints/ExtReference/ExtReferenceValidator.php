<?php
/**
 * ExtReferenceValidator class file
 */

namespace Graviton\RestBundle\Validator\Constraints\ExtReference;

use Graviton\DocumentBundle\Service\ExtReferenceConverterInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validator for the extref type
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ExtReferenceValidator extends ConstraintValidator
{
    /**
     * @var ExtReferenceConverterInterface
     */
    private $converter;

    /**
     * Inject extref converter
     *
     * @param ExtReferenceConverterInterface $converter Extref converter
     * @return void
     */
    public function setConverter(ExtReferenceConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed      $value      The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     * @return void
     * @throws \InvalidArgumentException
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ExtReference) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\ExtReference');
        }

        if ($value === null) {
            return;
        }

        try {
            $extref = $this->converter->getDbRef($value);
            if (is_array($constraint->allowedCollections) &&
                !in_array('*', $constraint->allowedCollections, true) &&
                !in_array($extref->{'$ref'}, $constraint->allowedCollections, true)
            ) {
                $this->context->addViolation($constraint->notAllowedMessage, ['%url%' => $value]);
            }
        } catch (\InvalidArgumentException $e) {
            $this->context->addViolation($constraint->invalidMessage, ['%url%' => $value]);
        }
    }
}
