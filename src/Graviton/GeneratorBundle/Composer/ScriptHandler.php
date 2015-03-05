<?php
/**
 * composer scripthandler
 */

namespace Graviton\GeneratorBundle\Composer;

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
class ScriptHandler
{
    /**
     * Composer variables are declared static so that an event could update
     * a composer.json and set new options, making them immediately available
     * to forthcoming listeners.
     */
    private static $options = array(
        'symfony-app-dir' => 'app',
    );

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
        $cmd = 'graviton:generate:dynamicbundles --json';

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
        $cmd = 'graviton:clean:dynamicbundles';

        self::executeCommand($event, $consolePath, $cmd);
    }

    /**
     * Returns some options - adapted from Sensio DistributionBundle Command
     *
     * @param CommandEvent $event Event
     *
     * @return array Options
     */
    protected static function getOptions(CommandEvent $event)
    {
        $options = array_merge(self::$options, $event->getComposer()->getPackage()->getExtra());
        $options['process-timeout'] = $event->getComposer()->getConfig()->get('process-timeout');
        return $options;
    }

    /**
     * Executes a command
     *
     * @param CommandEvent $event      Event
     * @param string       $consoleDir Console dir
     * @param string       $cmd        Command
     * @param int          $timeout    Timeout
     *
     * @return void
     */
    protected static function executeCommand(CommandEvent $event, $consoleDir, $cmd, $timeout = 300)
    {
        $php = escapeshellarg(self::getPhp(false));
        $console = escapeshellarg($consoleDir.'/console');
        if ($event->getIO()->isDecorated()) {
            $console .= ' --ansi';
        }

        $process = new Process($php.' '.$console.' '.escapeshellarg($cmd), null, null, null, $timeout);
        $process->run(
            function ($type, $buffer) use ($event) {
                $event->getIO()->write($buffer, false);
            }
        );

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(
                sprintf(
                    'An error occurred when executing the "%s" command.',
                    escapeshellarg($cmd)
                )
            );
        }
    }


    /**
     * Finds the path to the php executable
     *
     * @param bool $includeArgs include args
     *
     * @return bool|false|null|string path
     */
    protected static function getPhp($includeArgs = true)
    {
        $phpFinder = new PhpExecutableFinder();
        if (!$phpPath = $phpFinder->find($includeArgs)) {
            throw new \RuntimeException(
                'The php executable could not be found, add it to your PATH environment variable and try again'
            );
        }

        return $phpPath;
    }
}
