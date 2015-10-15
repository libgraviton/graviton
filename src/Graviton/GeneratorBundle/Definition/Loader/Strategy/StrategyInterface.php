<?php
/**
 * interface for definition loader strategies
 */

namespace Graviton\GeneratorBundle\Definition\Loader\Strategy;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
interface StrategyInterface
{
    /**
     * may the strategy handle this input
     *
     * @param string|null $input input from command
     *
     * @return boolean
     */
    public function supports($input);

    /**
     * load raw JSON data
     *
     * @param string|null $input input from command
     *
     * @return string[]
     */
    public function load($input);
}
