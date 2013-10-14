<?php
/**
 * Scripts for composers scripts api.
 */

namespace Graviton\BundleBundle\Composer;

use Composer\Script\CommandEvent;

/**
 * ScriptHandler
 *
 * @category GravitonBundleBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class ScriptHandler
{
    /**
     * build artifacts in app/.
     *
     * @param CommandEvent $event Event from composer
     *
     * @return void
     */
    public static function buildImportsFiles(CommandEvent $event)
    {
        $options = self::getOptions($event);
        $appDir = $options['symfony-app-dir'];

        if (!is_dir($appDir)) {
            echo 'The dir ('.$appDir.') was not found in '.getcwd().'.'.PHP_EOL;

            return;
        }

        static::doBuildConfigImportsFile($appDir, $options['packages']);
        static::doBuildRoutingImportsFile($appDir, $options['packages']);
    }

    /**
     * Build app/config-imports.xml
     *
     * @param String $appDir   base dir to create file in
     * @param Array  $packages Package configs
     *
     * @return void
     */
    public static function doBuildConfigImportsFile($appDir, $packages)
    {
        $file = $appDir.'/config-imports.xml';
        if (file_exists($file)) {
            unlink($file);
        }

        $xml  = '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
        $xml .= '<container xmlns="http://symfony.com/schema/dic/services">';
        $xml .= '<!-- This is a generated file. -->';

        if (true || !empty($packages)) {
            $imports = "";
            foreach ($packages as $package) {
                if (isset($package['extra']['graviton-config-import'])) {
                    $import = $package['dir'].'/';
                    $import .= $package['extra']['graviton-config-import'];
                    $imports .= '<import resource="'.$import.'"/>';
                }
            }
            if (!empty($imports)) {
                $xml .= '<imports>'.$imports.'</imports>';
            }
        }
        $xml .= '</container>';

        file_put_contents($file, $xml);
    }

    /**
     * Build app/routing-imports.xml
     *
     * @param String $appDir   base dir to create file in
     * @param Array  $packages Package configs
     *
     * @return void
     */
    public static function doBuildRoutingImportsFile($appDir, $packages)
    {
        $file = $appDir.'/routing-imports.xml';
        if (file_exists($file)) {
            unlink($file);
        }

        $xml  = '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
        $xml .= '<routes xmlns="http://symfony.com/schema/routing">';
        $xml .= '<!-- This is a generated file. -->';

        if (true || !empty($packages)) {
            $imports = "";
            foreach ($packages as $package) {
                if (isset($package['extra']['graviton-routing-import'])) {
                    $import = $package['dir'].'/';
                    $import .= $package['extra']['graviton-routing-import'];
                    $imports .= '<import resource="'.$import.'"/>';
                }
            }
            if (!empty($imports)) {
                $xml .= $imports;
            }
        }
        $xml .= '</routes>';

        file_put_contents($file, $xml);
    }

    /**
     * get options from composer.
     *
     * @param CommandEvent $event Event from composer
     *
     * @return Array
     */
    protected static function getOptions(CommandEvent $event)
    {
        $composer = $event->getComposer();
        $config = $composer->getConfig();
        $options = array_merge(
            array(
                'symfony-app-dir' => 'app',
                'symfony-web-dir' => 'web',
            ),
            $composer->getPackage()->getExtra()
        );

        $options['process-timeout'] = $config->get('process-timeout');

        $options['packages'] = self::getPackageExtraOptions($event);

        return $options;
    }

    /**
     * get extra options for all packages.
     *
     * Fetches all the composer.json files and grabs extra config if available.
     *
     * @param CommandEvent $event Event from composer
     *
     * @return Array
     */
    private static function getPackageExtraOptions(CommandEvent $event)
    {
        $packages = array();
        $vendorDir = __DIR__.'/../../../../';
        $vendorDir .= $event->getComposer()->getConfig()->get('vendor-dir');
        $lockData = $event->getComposer()->getLocker()->getLockData();
        foreach ($lockData['packages'] as $package) {
            $dir = $vendorDir.'/'.$package['name'];
            if (isset($package['target-dir'])) {
                $dir .= '/'.$package['target-dir'];
            }
            $file = $dir.'/composer.json';
            $packageJson = json_decode(file_get_contents($file), true);
            if (isset($packageJson['extra'])) {
                $packages[$package['name']] = array(
                    'dir' => $dir,
                    'extra' => $packageJson['extra']
                );
            }
        }

        return $packages;
    }
}
