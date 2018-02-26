<?php
/**
 * load json from a dir if json files are in a subdir called resources/definition
 */

namespace Graviton\GeneratorBundle\Definition\Loader\Strategy;

use Symfony\Component\Finder\Finder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
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
     * @param mixed $input Input from command
     * @return Finder
     */
    protected function getFinder($input)
    {
        return (new Finder())
            ->files()
            ->in($this->scanDir)
            ->name('*.json')
            ->notName('_*')
            ->path('/(^|\/)resources\/definition($|\/)/i')
            ->notPath('/(^|\/)Tests($|\/)/i');
    }
}
