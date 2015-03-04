<?php

namespace Graviton\GeneratorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Deletes the GravitonDyn/ folder..
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class CleanDynamicBundleCacheCommand extends ContainerAwareCommand
{

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
        $dynamicDir = $this->getContainer()->get('kernel')->getRootDir().'/../src/GravitonDyn/';

        $process = new Process('rm -Rf '.escapeshellarg($dynamicDir));
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }
    }
}
