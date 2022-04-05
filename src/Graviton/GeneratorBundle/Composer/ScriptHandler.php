<?php
/**
 * composer scripthandler
 */

namespace Graviton\GeneratorBundle\Composer;

use Graviton\CoreBundle\Composer\ScriptHandlerBase;
use Composer\Script\Event;

/**
 * ScriptHandler for Composer, wrapping our symfony console commands..
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ScriptHandler extends ScriptHandlerBase
{
    /**
     * Generates dynamic bundles
     *
     * @param CommandEvent $event Event
     *
     * @return void
     */
    public static function generateDynamicBundles(Event $event)
    {
        $options = self::getOptions($event);
        $consolePath = $options['symfony-app-dir'];

        self::executeCommand(
            $event,
            $consolePath,
            ['graviton:generate:dynamicbundles', '--json']
        );
    }
}
