<?php
/**
 * generator command
 */

namespace Graviton\GeneratorBundle\Command;

use Graviton\GeneratorBundle\Generator\BundleGenerator;
use Sensio\Bundle\GeneratorBundle\Command\GenerateBundleCommand as SymfonyGenerateBundleCommand;
use Sensio\Bundle\GeneratorBundle\Model\Bundle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * generator command
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class GenerateBundleCommand extends SymfonyGenerateBundleCommand
{
    /**
     * @var string
     */
    private $loaderBundleName;

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this->addOption(
            'loaderBundleName',
            'lbn',
            InputOption::VALUE_OPTIONAL,
            'Name of the bundle to manipulate, defaults to GravitonCoreBundle',
            'GravitonCoreBundle'
        )
            ->addOption(
                'deleteBefore',
                'delbef',
                InputOption::VALUE_OPTIONAL,
                'If a string, that directory will be deleted prior to generation',
                null
            )
            ->setName('graviton:generate:bundle')
            ->setDescription('Generates a graviton bundle');
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
        $this->loaderBundleName = $input->getOption('loaderBundleName');

        $deleteBefore = $input->getOption('deleteBefore');
        $fs = new Filesystem();
        if ($deleteBefore != null && $fs->exists($deleteBefore)) {
            $fs->remove($deleteBefore);
        }

        parent::execute(
            $input,
            $output
        );
    }

    /**
     * {@inheritDoc}
     * Add the new bundle to the BundleBundle loader infrastructure instead of main kernel
     *
     * @param OutputInterface $output output
     * @param KernelInterface $kernel kernel
     * @param Bundle          $bundle bundle
     *
     * @return string[]
     */
    protected function updateKernel(OutputInterface $output, KernelInterface $kernel, Bundle $bundle)
    {
        return;
    }

    /**
     * {@inheritDoc}
     * Don't check routing since graviton bundles usually get routed explicitly based on their naming.
     *
     * @param OutputInterface $output output
     * @param Bundle          $bundle bundle
     *
     * @return string[]
     */
    protected function updateRouting(OutputInterface $output, Bundle $bundle)
    {
        return [];
    }

    /**
     * {@inheritDoc}
     * Don't do anything with the configuration since we load our bundles dynamically using the bundle-bundle-bundle
     *
     * @param OutputInterface $output output
     * @param Bundle          $bundle bundle
     *
     * @return void
     */
    protected function updateConfiguration(OutputInterface $output, Bundle $bundle)
    {
        return;
    }

    /**
     * {@inheritDoc}
     * Use an overridden generator to make nicer code
     *
     * @return BundleGenerator
     */
    protected function createGenerator()
    {
        return new BundleGenerator();
    }
}
