<?php
/**
 * Generates versions.json
 */

namespace Graviton\CoreBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Generates version.json
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class VersionGenerateCommand extends Command
{

    /**
     * container
     *
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * filesystem
     *
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * root dir
     *
     * @var string
     */
    private $rootDir;

    /**
     * cache directory
     *
     * @var string
     */
    private $cacheDir;

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('graviton:core:generateVersion')
             ->setDescription(
                 'Generates versions.json in cache dir'
             );
    }

    /**
     * set container
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container service_container
     *
     * @return void
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * set filesystem
     *
     * @param mixed $filesystem filesystem
     *
     * @return void
     */
    public function setFilesystem($filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * sets cacheDir
     *
     * @param string $cacheDir cache directory
     *
     * @return void
     */
    public function setCacheDir($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * sets the root dir
     *
     * @param string $rootDir root dir
     *
     * @return void
     */
    public function setRootDir($rootDir)
    {
        $this->rootDir = $rootDir;
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
        /**
         * somehow as the swagger spec generation digs deep in the utils/router stuff,
         * somewhere Request is needed.. so we need to enter the request scope
         * manually.. maybe there is another possibility for this?
         */
        $this->container->enterScope('request');
        $this->container->set('request', new Request(), 'request');

        $this->filesystem->dumpFile(
            $this->cacheDir . '/core/versions.json',
            json_encode($this->getPackageVersions(), JSON_PRETTY_PRINT)
        );
    }

    /**
     * @return array version numbers of packages
     */
    public function getPackageVersions()
    {
        // -i installed packages
        $packageNames = shell_exec('composer show -i');
        $packages = explode(PHP_EOL, $packageNames);
        //last index is always empty
        array_pop($packages);

        $versions = array();
        foreach ($packages as $package) {
            preg_match_all('/([^\s]+)/', $package, $match);
            if (strpos($match[0][0], 'grv') === 0 | $match[0][0] === 'graviton') {
                $versions[$match[0][0]] = $match[0][1];
            }
        }
        $composerFile = !empty($composerFile) ? $composerFile : $this->rootDir . '/../composer.json';
        if (file_exists($composerFile)) {
            $composer = json_decode(file_get_contents($composerFile), true);
            if (JSON_ERROR_NONE === json_last_error() && !empty($composer['version'])) {
                $versions['graviton'] = $composer['version'];
            }
        }

        return $versions;
    }
}
