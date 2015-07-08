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
     * @inheritdoc
     */
    public function supports($input)
    {
        return is_file($input);
    }

    /**
     * @inheritdoc
     */
    protected function getJsonDefinitions($input)
    {
        return [
            file_get_contents($input),
        ];
    }
}
