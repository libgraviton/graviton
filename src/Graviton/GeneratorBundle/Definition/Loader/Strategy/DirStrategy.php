<?php
/**
 * load all json in a dir except those with _ at the start of their name
 */

namespace Graviton\GeneratorBundle\Definition\Loader\Strategy;

use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Finder\Finder;

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
     * load raw JSON data
     *
     * @param string|null $input input from command
     *
     * @return string[]
     */
    public function load($input)
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
            ->depth('== 0')
            ->sortByName();
    }
}
