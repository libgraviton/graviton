<?php
/**
 * strategy combining a set of strategies to be applied
 */

namespace Graviton\SecurityBundle\Authentication\Strategies;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Role\Role;

/**
 * Class MultiStrategy
 *
 * @package Graviton\SecurityBundle\Authentication\Strategies
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class MultiStrategy implements StrategyInterface
{
    /** @var StrategyInterface[]  */
    private $strategies;

    /** @var Role[] */
    private $roles= [];

    /**
     * MultiStrategy constructor.
     *
     * @param StrategyInterface[] $strategies List of strategies to be applied.
     */
    public function __construct(array $strategies)
    {
        $this->strategies = $strategies;
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
            $this->roles = $strategy->getRoles();

            if ($name && $strategy->stopPropagation()) {
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
     * @return Role[]
     */
    public function getRoles()
    {
        return array_unique($this->roles);
    }
}
