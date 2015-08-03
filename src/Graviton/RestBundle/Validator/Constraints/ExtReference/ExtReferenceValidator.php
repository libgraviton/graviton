<?php
/**
 * ExtReferenceValidator class file
 */

namespace Graviton\RestBundle\Validator\Constraints\ExtReference;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Route;

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
     * @var RouterInterface
     */
    private $router;

    /**
     * Inject router
     *
     * @param RouterInterface $router Router
     * @return void
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
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

        $path = parse_url($value, PHP_URL_PATH);
        if ($path !== false) {
            foreach ($this->router->getRouteCollection()->all() as $route) {
                if ($this->checkRoute($route, $path)) {
                    return;
                }
            }
        }

        $this->context->addViolation($constraint->message, ['%url%' => $value]);
    }

    /**
     * Check Route object
     *
     * @param Route  $route Route
     * @param string $path  Extref URL path
     * @return bool
     */
    private function checkRoute(Route $route, $path)
    {
        return $route->getRequirement('id') !== null &&
            $route->getMethods() === ['GET'] &&
            preg_match($route->compile()->getRegex(), $path);
    }
}
