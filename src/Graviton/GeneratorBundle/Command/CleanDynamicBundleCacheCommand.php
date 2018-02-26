<?php
/**
 * cleans dynamic bundle directory
 */

namespace Graviton\GeneratorBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Deletes the GravitonDyn/ folder..
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class CleanDynamicBundleCacheCommand extends Command
{

    /**
     * kernel
     *
     * @var \Graviton\AppKernel
     */
    private $kernel;

    /**
     * filesystem
     *
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('graviton:clean:dynamicbundles')
            ->setDescription(
                'Removes the folder with the generated dynamic bundles'
            );
    }

    /**
     * set kernel
     *
     * @param mixed $kernel kernel
     *
     * @return void
     */
    public function setKernel($kernel)
    {
        $this->kernel = $kernel;
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
     * {@inheritDoc}
     *
     * @param InputInterface  $input  input
     * @param OutputInterface $output output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // @todo it was suggested this may/should be moved to app/cache..?
        $dynamicDir = $this->kernel->getRootDir().'/../src/GravitonDyn/';

        if ($this->filesystem->exists($dynamicDir)) {
            $this->filesystem->remove($dynamicDir);
        }
    }
}
