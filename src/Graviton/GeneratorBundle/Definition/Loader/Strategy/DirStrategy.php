<?php
/**
 * load all JsonDefinitions in a dir except those with _ at the start of their name
 */

namespace Graviton\GeneratorBundle\Definition\Loader\Strategy;

use Graviton\GeneratorBundle\Definition\JsonDefinition;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DirStrategy implements StrategyInterface
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
        return is_dir($input);
    }

    /**
     * @param string|null $input input from command
     *
     * @return JsonDefinition[]
     */
    public function load($input)
    {
        $results = array();
        foreach ($this->getIterator($input) as $file) {
            if ($this->isValid($input, $file)) {
                $results[] = new JsonDefinition($file[0]);
            }
        }
        return $results;
    }

    /**
     * @param string|null $input
     * @return \RecursiveRegexIterator
     */
    protected function getIterator($input)
    {
        $directory = new \RecursiveDirectoryIterator($input);
        return new \RecursiveRegexIterator(
            $directory,
            '/.*\/[^_]\w+\.json$/i',
            \RecursiveRegexIterator::GET_MATCH
        );
    }

    /**
     * @param string|null $input
     * @return boolean
     */
    public function isValid($input, $file)
    {
        return true;
    }
}
