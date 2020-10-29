<?php
/**
 * load all json in a dir except those with _ at the start of their name
 */

namespace Graviton\GeneratorBundle\Definition\Loader\Strategy;

use Rs\Json\Patch;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Finder\Finder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
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
        $patches = $this->loadPatches($input);

        return array_map(
            function (SplFileInfo $file) use ($patches) {
                $content = $file->getContents();

                // patches?
                if (isset($patches[$file->getBasename()]) && is_array($patches[$file->getBasename()])) {
                    foreach ($patches[$file->getBasename()] as $patch) {
                        $patcher = new Patch($content, json_encode($patch));
                        $content = $patcher->apply();
                    }
                }

                return $content;
            },
            array_values(iterator_to_array($this->getFinder($input)))
        );
    }

    /**
     * finds all patches that should be applied to other documents
     *
     * @param string $input input
     *
     * @return array patches, key is target filename, value an array of patches
     */
    private function loadPatches($input)
    {
        $patches = [];
        foreach ($this->getFinderPatches($input) as $file) {
            $patchDef = json_decode($file->getContents(), false);
            if (!is_object($patchDef)) {
                throw new \RuntimeException('Could not parse patch definition in ' . $file->getPathname());
            }

            if (!isset($patchDef->targetFile) || !isset($patchDef->patch)) {
                throw new \RuntimeException('Missing properties "targetFile" or "patch" in ' . $file->getPathname());
            }

            $patches[$patchDef->targetFile][] = $patchDef->patch;
        }

        return $patches;
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

    /**
     * @param mixed $input Input from command
     * @return Finder
     */
    protected function getFinderPatches($input)
    {
        return (new Finder())
            ->files()
            ->in($input)
            ->name('*.json')
            ->path('/(^|\/)patches($|\/)/i')
            ->notName('_*')
            ->depth('== 0');
    }
}
