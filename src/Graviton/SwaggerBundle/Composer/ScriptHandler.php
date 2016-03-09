<?php
/**
 * swagger composer scripthandler
 */

namespace Graviton\SwaggerBundle\Composer;

use Graviton\CoreBundle\Composer\ScriptHandlerBase;
use Composer\Script\CommandEvent;

/**
 * ScriptHandler for Composer, wrapping our symfony console commands..
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ScriptHandler extends ScriptHandlerBase
{
    /**
     * Generates swagger.json
     *
     * @param CommandEvent $event Event
     *
     * @return void
     */
    public static function generateSwaggerJson(CommandEvent $event)
    {
        $options = self::getOptions($event);
        $consolePath = $options['symfony-app-dir'];
        $cmd = escapeshellarg('graviton:swagger:generate');

        self::executeCommand($event, $consolePath, $cmd);
    }
}
