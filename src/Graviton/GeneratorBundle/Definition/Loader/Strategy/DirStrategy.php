<?php
/**
 * load JsonDefinitions from a dir
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
    public function accepts($input)
    {
        return is_dir($input);
    }

    /**
     * try loading all .json files in a dir except those with at the start of their name
     *
     * @param string|null $input input from command
     *
     * @return JsonDefinition[]
     */
    public function load($input)
    {
        $directory = new \RecursiveDirectoryIterator($input);
        $jsonFiles = new \RecursiveRegexIterator(
            $directory,
            '/.*\/[^_]\w+\.json$/i',
            \RecursiveRegexIterator::GET_MATCH
        );

        $results = array();
        foreach ($jsonFiles as $file) {
            $results[] = new JsonDefinition($file[0]);
        }
        return $results;
    }
}
