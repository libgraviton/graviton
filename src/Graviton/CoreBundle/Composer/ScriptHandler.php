<?php
/**
 * core composer scriptHandler
 */

namespace Graviton\CoreBundle\Composer;

use Graviton\CoreBundle\Composer\ScriptHandlerBase;
use Graviton\CoreBundle\Service\CoreVersionUtils;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Filesystem\Filesystem;
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
     * Generates versions.yml
     *
     * @param CommandEvent $event Event
     *
     * @return void
     */
    public static function generateVersionYml(CommandEvent $event)
    {
        $baseDir = __DIR__.'/../../../..';
        $rootDir = $baseDir.'/app';

        if (self::hasComposerCommandInEnvVars()) {
            $coreVersionUtils = new CoreVersionUtils(
                self::getComposerCommand(),
                $rootDir,
                new Dumper
            );
            $filesystem = new Filesystem;

            // read version config using composer
            $coreVersionUtils->getVersionConfig();

            $filesystem->dumpFile(
                $baseDir . '/versions.yml',
                $coreVersionUtils->getPackageVersions()
            );
        }
    }

    /**
     * Finds the composer command set in environment var »COMPOSER_CMD«
     *
     * @return bool
     */
    private static function hasComposerCommandInEnvVars()
    {
        return false !== getenv('COMPOSER_CMD') && 'false' !== getenv('COMPOSER_CMD');
    }

    /**
     * Finds the composer command on defined for the current instance.
     *
     * @return string
     */
    private static function getComposerCommand()
    {
        $composerCommand = getenv('COMPOSER_CMD');

        if (empty($composerCommand)) {
            $composerCommand = 'composer';
        }

        return $composerCommand;
    }
}
