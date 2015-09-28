<?php
/**
 * Generates version.yml
 */

namespace Graviton\CoreBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates version.yml
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class VersionGenerateYml extends Command
{
    /**
     * root dir
     *
     * @var string
     */
    private $rootDir;

    /**
     * filesystem
     *
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * @var \Graviton\CoreBundle\Service\CoreVersionUtils
     */
    private $coreVersionUtils;

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('graviton:core:generate:versionYml')
             ->setDescription(
                 'Generates swagger.json in web dir'
             );
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
     * set CoreVersionUtils
     *
     * @param \Graviton\CoreBundle\Service\CoreVersionUtils $coreVersionUtils coreVersionUtils
     *
     * @return void
     */
    public function setCoreVersionUtils($coreVersionUtils)
    {
        $this->coreVersionUtils = $coreVersionUtils;
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

        $this->filesystem->dumpFile(
            $this->rootDir.'/../versions.yml',
            $this->coreVersionUtils->getPackageVersions()
        );
    }
}
