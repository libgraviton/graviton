<?php
/**
 * ExtReferenceValidator class file
 */

namespace Graviton\RestBundle\Validator\Constraints\ExtReference;

use Graviton\DocumentBundle\Service\ExtReferenceResolverInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

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
     * @var ExtReferenceResolverInterface
     */
    private $resolver;

    /**
     * Inject extref resolver
     *
     * @param ExtReferenceResolverInterface $resolver Extref resolver
     * @return void
     */
    public function setResolver(ExtReferenceResolverInterface $resolver)
    {
        $this->resolver = $resolver;
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
            throw new \InvalidArgumentException(
                sprintf(
                    'Constraint must be instance of %s (%s given)',
                    'Graviton\RestBundle\Validator\Constraints\ExtReference\ExtReference',
                    get_class($constraint)
                )
            );
        }

        try {
            $this->resolver->getDbValue($value);
        } catch (\InvalidArgumentException $e) {
            $this->context->addViolation($constraint->message, ['%url%' => $value]);
        }
    }
}
