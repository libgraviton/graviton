<?php
/** A custom compiler pass class */

namespace Graviton\CoreBundle\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use \Symfony\Component\Yaml\Parser;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class VersionCompilerPass implements CompilerPassInterface
{

    /**
     * @var rootDir
     */
    private $rootDir;

    /**
     * @var config
     */
    private $config;

    /**
     * add version numbers of packages to the container
     *
     * @param ContainerBuilder $container Container
     *
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $this->rootDir = $container->getParameter('kernel.root_dir');
        $this->config = $this->getVersionConfig();
        $container->setParameter(
            'graviton.core.version.data',
            $this->getPackageVersions()
        );
    }

    /**
     * gets all versions
     *
     * @return array version numbers of packages
     */
    private function getPackageVersions()
    {
        if ($this->isDesiredVersion('self')) {
            $versions = [
                $this->getContextVersion(),
            ];
        } else {
            $versions = array();
        }
        $versions = $this->getInstalledPackagesVersion($versions);

        return $versions;
    }

    /**
     * returns the version of graviton or wrapper
     *
     * @return array
     */
    private function getContextVersion()
    {
        $output = $this->runCommandInContext('composer show -s --no-ansi', $this->rootDir);
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
        $output = $this->runCommandInContext('composer show -i');

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
     * runs a bash/shell command depending on the context
     *
     * @param string $command shell/bash command
     * @return string
     */
    private function runCommandInContext($command)
    {
        if ($this->isWrapperContext()) {
            $process = new Process('cd ' . escapeshellarg($this->rootDir) . '/../../../../  && ' . $command);
        } else {
            $process = new Process('cd ' . escapeshellarg($this->rootDir) . '/../ && ' . $command);
        }

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
     */
    private function isDesiredVersion($packageName)
    {
        foreach ($this->config['desiredVersions'] as $confEntry) {
            if ($confEntry == $packageName) {
                return true;
            }
        }

        return false;
    }

    /**
     * read and parses version config file
     *
     * @return array
     */
    private function getVersionConfig()
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

        return $parsedConfig;
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
