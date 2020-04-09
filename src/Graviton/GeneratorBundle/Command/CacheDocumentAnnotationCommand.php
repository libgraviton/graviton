<?php
/**
 * cache document annotations
 */

namespace Graviton\GeneratorBundle\Command;

use Graviton\DocumentBundle\Annotation\ClassScanner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Here, we generate all "dynamic" Graviton bundles..
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class CacheDocumentAnnotationCommand extends Command
{

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->addOption(
                'srcDir',
                '',
                InputOption::VALUE_OPTIONAL,
                'Src Dir',
                dirname(__FILE__) . '/../../../'
            )
            ->setName('graviton:cache:document-annotations')
            ->setDescription('Caches our document annotations for runtime use.');
    }

    /**
     * {@inheritDoc}
     *
     * @param InputInterface  $input  input
     * @param OutputInterface $output output
     *
     * @return int exit code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $srcDir = explode(',', $input->getOption('srcDir'));
        $driver = ClassScanner::getDocumentDriver($srcDir);

        $annotations = [];
        foreach ($driver->getAllClassNames() as $className) {
            $annotations[$className] = $driver->getFields($className);
        }

        $fs = new Filesystem();
        $fs->dumpFile($driver->getCacheLocation(), serialize($annotations));

        $output->writeln('Wrote document annotations to file '.$driver->getCacheLocation());

        return 0;
    }
}
