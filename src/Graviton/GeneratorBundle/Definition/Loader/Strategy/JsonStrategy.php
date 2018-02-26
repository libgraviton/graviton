<?php
namespace Graviton\GeneratorBundle\Definition\Loader\Strategy;

/**
 * Load definition from JSON string
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class JsonStrategy implements StrategyInterface
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
        return is_string($input) && strlen($input) > 0 && $input[0] === '{';
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
        return [$input];
    }
}
