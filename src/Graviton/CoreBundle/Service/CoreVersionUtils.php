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
        // get current commit hash
        $currentHash = trim($this->runGitInContext('rev-parse --short HEAD'));
        // get version from hash:
        $version = trim($this->runGitInContext('tag --points-at '.$currentHash));
        // if empty, set dev- and current branchname to version:
        if (!strlen($version)) {
            $version = 'dev-'.trim($this->runGitInContext('rev-parse --abbrev-ref HEAD'));
        }

        $wrapper['id'] = 'self';
        $wrapper['version'] = $version;

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
     * runs a git command depending on the context
     *
     * @param string $command git args
     * @return string
     *
     * @throws \RuntimeException
     * @throws \LogicException
     */
    private function runGitInContext($command)
    {
        $path =  ($this->isWrapperContext())
            ? $this->rootDir.'/../../../../'
            : $this->rootDir.'/../';
        $contextDir = escapeshellarg($path);
        $process = new Process('cd '.$contextDir.' && git '.$command);
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
     * Returns the version out of a given version string
     *
     * @param string $versionString SemVer version string
     * @return string
     */
    public function getVersionNumber($versionString)
    {
        try {
            $version = $this->getVersionOrBranchName($versionString);
        } catch (InvalidArgumentException $e) {
            $version = $this->normalizeVersionString($versionString);
        }

        return empty($version) ? $versionString : $version;
    }

    /**
     * Get a version string string using a regular expression
     *
     * @param string $versionString SemVer version string
     * @return string
     */
    private function getVersionOrBranchName($versionString)
    {
        // Regular expression for root package ('self') on a tagged version
        $tag = '^(?<version>[v]?[0-9]+\.[0-9]+\.[0-9]+)(?<prerelease>-[0-9a-zA-Z.]+)?(?<build>\+[0-9a-zA-Z.]+)?$';
        // Regular expression for root package on a git branch
        $branch = '^(?<branch>(dev\-){1}[0-9a-zA-Z\.\/\-\_]+)$';
        $regex = sprintf('/%s|%s/', $tag, $branch);

        $matches = [];
        if (0 === preg_match($regex, $versionString, $matches)) {
            throw new InvalidArgumentException(
                sprintf('"%s" is not a valid SemVer', $versionString)
            );
        }

        return empty($matches['version']) ? $matches['branch'] : $matches['version'];
    }

    /**
     * Normalizing the incorrect SemVer string to a valid one
     *
     * At the moment, we are getting the version of the root package ('self') using the
     * 'composer show -s'-command. Unfortunately Composer is adding an unnecessary ending.
     *
     * @param string $versionString SemVer version string
     * @param string $prefix        Version prefix
     * @return string
     */
    private function normalizeVersionString($versionString, $prefix = 'v')
    {
        if (substr_count($versionString, '.') === 3) {
            return sprintf(
                '%s%s',
                $prefix,
                implode('.', explode('.', $versionString, -1))
            );
        }
        return $versionString;
    }
}
