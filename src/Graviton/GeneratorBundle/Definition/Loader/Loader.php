<?php
/**
 * load definitions from a source
 *
 * This Loader implements the following strategies.
 * - file
 * - directory
 * - scan
 * - mongodb
 */

namespace Graviton\GeneratorBundle\Definition\Loader;

use Graviton\GeneratorBundle\Definition\Loader\Strategy\StrategyInterface;
use Graviton\GeneratorBundle\Definition\JsonDefinition;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class Loader implements LoaderInterface
{
    /**
     * @var StrategyInterface[]
     */
    protected $strategies = array();

    /**
     * add a strategy to the loader
     *
     * @param StrategyInterface $strategy strategy to add
     *
     * @return Loader
     */
    public function addStrategy(StrategyInterface $strategy)
    {
        $this->strategies[] = $strategy;
    }

    /**
     * load from input
     *
     * @param string|null $input input from command
     *
     * @return JsonDefinition[]
     */
    public function load($input)
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($input)) {
                return $strategy->load($input);
            }
        }

        return [];
    }
}
