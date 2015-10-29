<?php
/**
 * A service providing functions for getting version numbers
 */

namespace Graviton\CoreBundle\Service;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Process\Process;
use InvalidArgumentException;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class CoreVersionUtils
{
    /**
     * @var string
     */
    private $composerCmd;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var \Symfony\Component\Yaml\Dumper
     */
    private $yamlDumper;

    /**
     * @param string                         $composerCmd ComposerCommand
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
                $wrapper['version'] = $this->checkVersionNumber(
                    trim(str_replace('*', '', $wrapperVersionArr[1]))
                );
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
                array_push(
                    $versions,
                    array(
                        'id' => $content[0],
                        'version' => $content[1]
                    )
                );
            }
        }

        return $versions;
    }

    /**
     * runs a composer command depending on the context
     *
     * @param string $command composer args
     * @return string
     *
     * @throws \RuntimeException
     * @throws \LogicException
     */
    private function runComposerInContext($command)
    {
        $path =  ($this->isWrapperContext())
            ? $this->rootDir.'/../../../../'
            : $this->rootDir.'/../';
        $contextDir = escapeshellarg($path);
        $process = new Process('cd '.$contextDir.' && '.escapeshellcmd($this->composerCmd).' '.$command);
        $process->mustRun();

        return $process->getOutput();
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
     * @return string
     */
    public function getVersionConfig()
    {
        $filePath = $this->isWrapperContext()
            ? $this->rootDir . '/../../../../app/config/version_service.yml'
            : $this->rootDir . '/config/version_service.yml';

        return $this->getConfiguration($filePath);
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

    /**
     * reads configuration information from the given file into an array.
     *
     * @param string $filePath Absolute path to the configuration file.
     *
     * @return array
     */
    private function getConfiguration($filePath)
    {
        $parser = new Parser();
        $config = $parser->parse(file_get_contents($filePath));

        return is_array($config) ? $config : [];
    }

    /**
     * Checks if the version number is following semantic versioning
     *
     * @param string $versionString SemVer version string
     * @return string
     */
    public function checkVersionNumber($versionString)
    {
        try {
            $version = $this->semVerMatcher($versionString);
        } catch (InvalidArgumentException $e) {
            $version = $this->cutVersionString($versionString);
        }

        return $version;
    }

    /**
     * Match a SemVer string using a regex
     *
     * @param string $versionString SemVer version string
     * @return string
     */
    private function semVerMatcher($versionString)
    {
        $matches = array();

        // Regular expression for root package ('self') on a tagged version
        $tag = '^(?<version>[v]?[0-9]+\.[0-9]+\.[0-9]+)(?<prerelease>-[0-9a-zA-Z.]+)?(?<build>\+[0-9a-zA-Z.]+)?$';
        // Regular expression for root package on a git branch
        $branch = '^(?<branch>(dev\-){1}[0-9a-zA-Z\.\/\-\_]+)$';
        $regex = sprintf('/%s|%s/', $tag, $branch);

        $matched = preg_match($regex, $versionString, $matches);

        if (!$matched) {
            throw new InvalidArgumentException(
                sprintf('"%s" is not a valid SemVer', $versionString)
            );
        }

        return !empty($matches['version']) ? $matches['version'] : $matches['branch'];
    }

    /**
     * Changing the incorrect SemVer string to a valid one
     *
     * At the moment, we are getting the version of the root package ('self') using the
     * 'composer show -s'-command. Unfortunately Composer is adding an unnecessary ending.
     *
     * @param string $versionString SemVer version string
     * @return string
     */
    private function cutVersionString($versionString)
    {
        $versionParts = explode('.', $versionString);

        if (substr_count($versionString, '.') === 3) {
            $versionString = sprintf(
                'v%d.%d.%d',
                $versionParts[0],
                $versionParts[1],
                $versionParts[2]
            );
        } elseif (substr_count($versionString, '.') === 1) {
            $versionString = sprintf(
                'v%d.%d.0',
                $versionParts[0],
                $versionParts[1]
            );
        }

        return $versionString;
    }
}
