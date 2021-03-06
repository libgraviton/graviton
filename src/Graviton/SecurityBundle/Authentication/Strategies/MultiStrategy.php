<?php
/**
 * strategy combining a set of strategies to be applied
 */

namespace Graviton\SecurityBundle\Authentication\Strategies;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class MultiStrategy
 *
 * @package Graviton\SecurityBundle\Authentication\Strategies
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class MultiStrategy implements StrategyInterface
{
    /** @var StrategyInterface[]  */
    private $strategies = [];

    /** @var Role[] */
    private $roles = [];

    /**
     * set strategies
     *
     * @param ContainerInterface $container  container
     * @param array              $strategies array of strategy service names to use.
     *
     * @return void
     */
    public function setStrategies(ContainerInterface $container, array $strategies)
    {
        foreach ($strategies as $strategy) {
            if (($strategyService = $container->get($strategy)) instanceof StrategyInterface) {
                $this->strategies[] = $strategyService;
            }
        }
    }

    /**
     * Applies the defined strategies on the provided request.
     *
     * @param Request $request request to handle
     *
     * @return string
     */
    public function apply(Request $request)
    {
        foreach ($this->strategies as $strategy) {
            $name = $strategy->apply($request);
            if ($strategy->stopPropagation()) {
                $this->roles = $strategy->getRoles();
                return $name;
            }
        }

        return false;
    }

    /**
     * Decider to stop other strategies running after from being considered.
     *
     * @return boolean
     */
    public function stopPropagation()
    {
        return false;
    }

    /**
     * Provides the list of registered roles.
     *
     * @return string[] roles
     */
    public function getRoles()
    {
        return array_unique($this->roles);
    }
}
