<?php
namespace Graviton\GeneratorBundle\Definition\Loader\Strategy;

/**
 * Load definition from JSON string
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class JsonStrategy extends AbstractStrategy
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
     * @param mixed $input Input from command
     * @return string[]
     */
    public function getRawDefinitions($input)
    {
        return [$input];
    }
}
