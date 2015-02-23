<?php
/**
 * interface for getting definitions
 */

namespace Graviton\GeneratorBundle\Definition\Loader;

use Graviton\GeneratorBundle\Definition\Loader\Strategy\StrategyInterface;
use Graviton\GeneratorBundle\Definition\JsonDefinition;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
interface LoaderInterface
{
    /**
     * add a strategy to the loader
     *
     * @param StrategyInterface $strategy strategy to add
     *
     * @return Loader
     */
    public function addStrategy(StrategyInterface $strategy);

    /**
     * @param string|null $input input from command
     *
     * @return JsonDefinition[]
     */
    public function load($input);
}
