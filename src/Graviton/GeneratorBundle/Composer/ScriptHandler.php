<?php
/**
 * composer scripthandler
 */

namespace Graviton\GeneratorBundle\Composer;

use Graviton\CoreBundle\Composer\ScriptHandlerBase;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;
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
     * Generates dynamic bundles
     *
     * @param CommandEvent $event Event
     *
     * @return void
     */
    public static function generateDynamicBundles(CommandEvent $event)
    {
        $options = self::getOptions($event);
        $consolePath = $options['symfony-app-dir'];
        $cmd = escapeshellarg('graviton:generate:dynamicbundles').' --json';

        self::executeCommand($event, $consolePath, $cmd);
    }

    /**
     * Cleans existing dynamic bundles
     *
     * @param CommandEvent $event Event
     *
     * @return void
     */
    public static function cleanDynamicBundles(CommandEvent $event)
    {
        $options = self::getOptions($event);

        $consolePath = $options['symfony-app-dir'];
        $cmd = escapeshellarg('graviton:clean:dynamicbundles');

        self::executeCommand($event, $consolePath, $cmd);
    }
}
