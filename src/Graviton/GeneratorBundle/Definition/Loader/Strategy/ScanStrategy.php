<?php
/**
 * load JsonDefinition from a file
 */

namespace Graviton\GeneratorBundle\Definition\Loader\Strategy;

use Graviton\GeneratorBundle\Definition\JsonDefinition;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ScanStrategy implements StrategyInterface
{
    /**
     * @var string
     */
    protected $scanDir;

    /**
     * @param string $scanDir dir to scan
     *
     * @todo this facility used to use $input->getOption('srcDir').'../' in non-vendorized mode. why?
     */
    public function setScanDir($scanDir)
    {
        // if we are vendorized we will search all vendor paths
        if (strpos($scanDir, 'vendor/graviton/graviton')) {
            $scanDir += '/../../';
        }
        $this->scanDir = $scanDir;
    }

    /**
     * may the strategy handle this input
     *
     * @param string|null $input input from command
     *
     * @return boolean
     */
    public function accepts($input)
    {
        return is_null($input);
    }

    /**
     * load
     *
     * @param string|null $input input from command
     *
     * @return JsonDefinition[]
     */
    public function load($input)
    {
        $testMode = strpos($input, '/Tests/') !== 0;

        // find json files with resources/definition in their path
        $directory = new \RecursiveDirectoryIterator($this->scanDir);
        $iterator = new \RecursiveIteratorIterator($directory);
        $jsonFiles = new \RegexIterator(
            $iterator,
            '/.*\/resources\/definition\/[^_].+\.json$/i',
            \RegexIterator::GET_MATCH
        );

        $results = array();
        foreach ($jsonFiles as $file) {
            // skip files in Tests dirs (wasn't easy to do in the regex above to it's here)
            if ($testMode || !strpos($file[0], '/Tests/')) {
                $results[] = new JsonDefinition($file[0]);
            }
        }
        return $results;
    }
}
