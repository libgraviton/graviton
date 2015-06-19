<?php
/**
 * load JsonDefinition from a dir if json files are in a subdir called resources/definition
 */

namespace Graviton\GeneratorBundle\Definition\Loader\Strategy;

use Graviton\GeneratorBundle\Definition\JsonDefinition;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ScanStrategy extends DirStrategy
{
    /**
     * @var string
     */
    protected $scanDir;

    /**
     * @param string $scanDir dir to scan
     *
     * @return void
     */
    public function setScanDir($scanDir)
    {
        // if we are vendorized we will search all vendor paths
        if (strpos($scanDir, 'vendor/graviton/graviton')) {
            $scanDir .= '/../../';
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
    public function supports($input)
    {
        return is_null($input);
    }

    /**
     * @param string|null $input input from command
     *
     * @return \RegexIterator
     */
    protected function getIterator($input)
    {
        $directory = new \RecursiveDirectoryIterator($this->scanDir);
        $iterator = new \RecursiveIteratorIterator($directory);
        return new \RegexIterator(
            $iterator,
            '/.*\/resources\/definition\/[^_].+\.json$/i',
            \RegexIterator::GET_MATCH
        );
    }

    /**
     * @param string|null $input input from command
     * @param array       $file  input from command
     *
     * @return boolean
     */
    public function isValid($input, $file)
    {
        $checkFile = str_replace($this->scanDir, '', $file[0]);
        return strpos($this->scanDir, '/Tests/') || !strpos($checkFile, '/Tests/');
    }
}
