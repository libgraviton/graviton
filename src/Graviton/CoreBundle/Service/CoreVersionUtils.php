<?php
/**
 * A service providing functions for getting version numbers
 */

namespace Graviton\CoreBundle\Service;

use \Symfony\Component\Yaml\Parser;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class CoreVersionUtils
{
    /**
     * @var ?
     */
    private $composerCmd;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @param ComposerCmd                    $composerCmd ComposerCommand
     * @param string                         $rootDir     Path to root dir
     * @param \Symfony\Component\Yaml\Dumper $yamlDumper  Yaml dumper
     */
    public function __construct($composerCmd, $rootDir, $yamlDumper)
    {
        $this->composerCmd = $composerCmd;
        $this->rootDir = $rootDir;
        $this->yamlDumper = $yamlDumper;
    }

    /**
     * gets all versions
     *
     * @return array version numbers of packages
     */
    public function getPackageVersions()
    {
        if ($this->isDesiredVersion('self')) {
            $versions = [
                $this->getContextVersion(),
            ];
        } else {
            $versions = array();
        }
        $versions = $this->getInstalledPackagesVersion($versions);

        return $this->yamlDumper->dump($versions);
    }

    /**
     * returns the version of graviton or wrapper
     *
     * @return array
     */
    private function getContextVersion()
    {
        $output = $this->runComposerInContext('show -s --no-ansi');
        $lines = explode(PHP_EOL, $output);
        $wrapper = array();
        foreach ($lines as $line) {
            if (strpos($line, 'versions') !== false) {
                $wrapperVersionArr = explode(':', $line);
                $wrapper['id'] = 'self';
                $wrapper['version'] = trim(str_replace('*', '', $wrapperVersionArr[1]));
            }
        }

        return $wrapper;
    }

    /**
     * returns version for every installed package
     *
     * @param array $versions versions array
     * @return array
     */
    private function getInstalledPackagesVersion($versions)
    {
        $output = $this->runComposerInContext('show --installed');

        $packages = explode(PHP_EOL, $output);
        //last index is always empty
        array_pop($packages);

        foreach ($packages as $package) {
            $content = preg_split('/([\s]+)/', $package);
            if ($this->isDesiredVersion($content[0])) {
                array_push($versions, array('id' => $content[0], 'version' => $content[1]));
            }
        }

        return $versions;
    }

    /**
     * runs a composer command depending on the context
     *
     * @param string $command composer args
     * @return string
     */
    private function runComposerInContext($command)
    {
        if ($this->isWrapperContext()) {
            $contextDir = escapeshellarg($this->rootDir.'/../../../../');
        } else {
            $contextDir = escapeshellarg($this->rootDir.'/../');
        }

        $process = new Process('cd '.$contextDir.' && '.escapeshellcmd($this->composerCmd).' '.$command);

        try {
            $process->mustRun();

            return $process->getOutput();
        } catch (ProcessFailedException $e) {
            echo $e->getMessage();
        }
    }

    /**
     * checks if the package version is configured
     *
     * @param string $packageName package name
     * @return boolean
     *
     * @throws \RuntimeException
     */
    private function isDesiredVersion($packageName)
    {
        if (empty($packageName)) {
            throw new \RuntimeException('Missing package name');
        }

        $config = $this->getVersionConfig();

        if (!empty($config['desiredVersions'])) {
            foreach ($config['desiredVersions'] as $confEntry) {
                if ($confEntry == $packageName) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * read and parses version config file
     *
     * @return void
     */
    public function getVersionConfig()
    {
        $parser = new Parser();
        if ($this->isWrapperContext()) {
            $parsedConfig = $parser->parse(
                file_get_contents($this->rootDir . '/../../../../app/config/version_service.yml')
            );
        } else {
            $parsedConfig = $parser->parse(
                file_get_contents($this->rootDir . '/config/version_service.yml')
            );
        }

        $this->config = $parsedConfig;
    }

    /**
     * checks if context is a wrapper or not
     *
     * @return boolean
     */
    private function isWrapperContext()
    {
        if (strpos($this->rootDir, 'vendor')) {
            return true;
        } else {
            return false;
        }
    }
}
