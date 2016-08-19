<?php
/**
 * Generates the versions.yml file
 */

namespace Graviton\CoreBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use InvalidArgumentException;

/**
 * Reads out the used versions with composer and git and writes them in a file 'versions.yml'
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class GenerateVersionsCommand extends Command
{
    /**
     * @var string
     */
    private $composerCmd;

    /**
     * @var string
     */
    private $contextDir;

    /*
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('graviton:core:generateversions')
            ->setDescription(
                'Generates the versions.yml file according to definition in app/config/version_service.yml'
            );
    }

    /**
     * {@inheritDoc}
     *
     * @param InputInterface  $input  input
     * @param OutputInterface $output output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getApplication()->getKernel()->getContainer();
        $rootDir = $container->getParameter('kernel.root_dir');
        $this->composerCmd = $container->getParameter('graviton.composer.cmd');

        $this->contextDir = $rootDir . '/../';
        if (strpos($rootDir, 'vendor')) {
            $this->contextDir = $rootDir . '/../../../../';
        }

        $this->output = $output;

        $filesystem = new Filesystem();
        $filesystem->dumpFile(
            $rootDir . '/../versions.yml',
            $this->getPackageVersions()
        );
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

        $yamlDumper = new Dumper();
        return $yamlDumper->dump($versions);
    }

    /**
     * returns the version of graviton or wrapper using git
     *
     * @return array
     */
    private function getContextVersion()
    {
        // get current commit hash
        $currentHash = trim($this->runGitInContext('rev-parse --short HEAD'));
        // get version from hash:
        $version = trim($this->runGitInContext('tag --points-at ' . $currentHash));
        // if empty, set dev- and current branchname to version:
        if (!strlen($version)) {
            $version = 'dev-' . trim($this->runGitInContext('rev-parse --abbrev-ref HEAD'));
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
     */
    private function runComposerInContext($command)
    {
        $process = new Process(
            'cd ' . escapeshellarg($this->contextDir)
            . ' && ' . escapeshellcmd($this->composerCmd)
            . ' ' . $command
        );
        try {
            $process->mustRun();
        } catch (ProcessFailedException $pFe) {
            $this->output->writeln($pFe->getMessage());
        }
        return $process->getOutput();
    }

    /**
     * runs a git command depending on the context
     *
     * @param string $command git args
     * @return string
     *
     * @throws \RuntimeException
     */
    private function runGitInContext($command)
    {
        $process = new Process('cd ' . escapeshellarg($this->contextDir) . ' && git ' . $command);
        try {
            $process->mustRun();
        } catch (ProcessFailedException $pFe) {
            $this->output->writeln($pFe->getMessage());
        }
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

        $config = $this->getConfiguration($this->contextDir . "/app/config/version_service.yml");

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
     * @param string $versionString SemVer  version string
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
