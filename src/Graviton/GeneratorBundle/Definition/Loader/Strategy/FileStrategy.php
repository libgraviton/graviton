<?php
/**
 * load JsonDefinition from a file
 */

namespace Graviton\GeneratorBundle\Definition\Loader\Strategy;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class FileStrategy extends AbstractStrategy
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
     * @param mixed $input Input from command
     * @return string[]
     */
    protected function getRawDefinitions($input)
    {
        return [file_get_contents($input)];
    }
}
