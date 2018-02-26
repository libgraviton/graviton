<?php
/**
 * load json from a file
 */

namespace Graviton\GeneratorBundle\Definition\Loader\Strategy;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class FileStrategy implements StrategyInterface
{
    /**
     * may the strategy handle this input
     *
     * @param string|null $input input from command
     *
     * @return boolean
     */
    public function supports($input)
    {
        return is_file($input);
    }

    /**
     * load raw JSON data
     *
     * @param string|null $input input from command
     *
     * @return string[]
     */
    public function load($input)
    {
        return [file_get_contents($input)];
    }
}
