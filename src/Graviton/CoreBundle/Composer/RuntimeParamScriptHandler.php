<?php
/**
 * composer scripthandler for runtime envs
 */

namespace Graviton\CoreBundle\Composer;

use Composer\Script\Event;
use Symfony\Component\Yaml\Inline;
use Symfony\Component\Yaml\Yaml;

/**
 * Base class for Composer ScriptHandlers
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RuntimeParamScriptHandler
{
    /**
     * build params
     *
     * @param Event $event event
     * @return void
     */
    public static function buildParameters(Event $event)
    {
        $extras = $event->getComposer()->getPackage()->getExtra();
        if (!isset($extras['incenteev-parameters'])) {
            return;
        }

        $parameters = [];
        foreach ($extras['incenteev-parameters'] as $file) {
            if (!empty($file['env-map'])) {
                foreach ($file['env-map'] as $paramName => $envName) {
                    if (isset($_ENV[$envName])) {
                        $parameters[$paramName] = Inline::parse($_ENV[$envName]);
                    }
                }
            }
        }

        $yaml = [
            'parameters' => $parameters
        ];

        file_put_contents(
            'app/config/parameters_runtime.yml',
            "# This file is auto-generated during the composer install\n" . Yaml::dump($yaml, 99)
        );

        $event->getIO()->write('<info>Wrote file app/config/parameters_runtime.yml</info>');
    }
}
