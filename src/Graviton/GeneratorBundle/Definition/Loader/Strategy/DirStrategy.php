<?php
/**
 * load all JsonDefinitions in a dir except those with _ at the start of their name
 */

namespace Graviton\GeneratorBundle\Definition\Loader\Strategy;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Finder\Finder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DirStrategy extends AbstractStrategy
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
     * @param mixed $input Input from command
     * @return string[]
     */
    protected function getRawDefinitions($input)
    {
        return array_map(
            function (SplFileInfo $file) {
                return $file->getContents();
            },
            array_values(iterator_to_array($this->getFinder($input)))
        );
    }

    /**
     * @param mixed $input Input from command
     * @return Finder
     */
    protected function getFinder($input)
    {
        return (new Finder())
            ->files()
            ->in($input)
            ->name('*.json')
            ->notName('_*')
            ->depth('== 0');
    }
}
