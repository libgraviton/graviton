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

        $coreVersionUtils = new CoreVersionUtils(
            getenv('COMPOSER_CMD') ? getenv('COMPOSER_CMD') : 'composer',
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
