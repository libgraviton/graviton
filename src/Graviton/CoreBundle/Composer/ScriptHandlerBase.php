<?php
/**
 * composer scripthandler base class
 */

namespace Graviton\CoreBundle\Composer;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;
use Composer\Script\Event;

/**
 * Base class for Composer ScriptHandlers
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
abstract class ScriptHandlerBase
{
    /**
     * Composer variables are declared static so that an event could update
     * a composer.json and set new options, making them immediately available
     * to forthcoming listeners.
     */
    protected static $options = array(
        'symfony-app-dir' => 'app',
    );

    /**
     * Returns some options - adapted from Sensio DistributionBundle Command
     *
     * @param CommandEvent $event Event
     *
     * @return array Options
     */
    protected static function getOptions(Event $event)
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
     * @param array        $cmd        Command
     * @param int          $timeout    Timeout
     *
     * @return void
     */
    protected static function executeCommand(Event $event, $consoleDir, array $cmd, $timeout = 300)
    {
        $command = [self::getPhp(false), $consoleDir.'/console'];

        if ($event->getIO()->isDecorated()) {
            $command[] = '--ansi';
        }

        $command = array_merge($command, $cmd);
        $process = new Process($command, null, null, null, $timeout);
        $process->run(
            function ($type, $buffer) use ($event) {
                $event->getIO()->write($buffer, false);
            }
        );

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(
                sprintf(
                    'An error occurred when executing the "%s" command.',
                    implode(' ', $command)
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
