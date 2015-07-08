<?php
/**
 * load all JsonDefinitions in a dir except those with _ at the start of their name
 */

namespace Graviton\GeneratorBundle\Definition\Loader\Strategy;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DirStrategy extends AbstractStrategy implements DirStrategyInterface
{
    /**
     * @inheritdoc
     */
    public function supports($input)
    {
        return is_dir($input);
    }

    /**
     * @inheritdoc
     */
    public function getJsonDefinitions($input)
    {
        $results = [];
        foreach ($this->getIterator($input) as $file) {
            if ($this->isValid($input, $file)) {
                $results[] = file_get_contents($file[0]);
            }
        }
        return $results;
    }

    /**
     * @inheritdoc
     */
    public function isValid($input, $file)
    {
        return true;
    }

    /**
     * @param string $dirname input value
     * @return \Iterator matched files
     */
    protected function getIterator($dirname)
    {
        return new \RecursiveRegexIterator(
            new \RecursiveDirectoryIterator($dirname),
            '/.*\/[^_]\w+\.json$/i',
            \RecursiveRegexIterator::GET_MATCH
        );
    }
}
