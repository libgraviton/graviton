<?php
/**
 * apply a strategy to a request
 */

namespace Graviton\SecurityBundle\Authentication\Strategies;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Role\Role;

/**
 * Interface StrategyInterface
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
interface StrategyInterface
{
    /**
     * Applies the defined strategy on the provided request.
     *
     * @param Request $request request to handle
     *
     * @return string
     */
    public function apply(Request $request);

    /**
     * Decider to stop other strategies running after from being considered.
     *
     * @return boolean
     */
    public function stopPropagation();

    /**
     * Provides the list of registered roles.
     *
     * @return Role[]
     */
    public function getRoles();
}
