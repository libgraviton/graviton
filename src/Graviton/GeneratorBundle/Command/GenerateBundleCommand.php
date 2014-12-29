<?php
namespace Graviton\GeneratorBundle\Command;

use Graviton\GeneratorBundle\Generator\BundleGenerator;
use Graviton\GeneratorBundle\Manipulator\BundleBundleManipulator;
use Sensio\Bundle\GeneratorBundle\Command\GenerateBundleCommand as SymfonyGenerateBundleCommand;
use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * generator command
 *
 * @category GeneratorBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class GenerateBundleCommand extends SymfonyGenerateBundleCommand
{

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
                 'doUpdateKernel',
                 'dak',
                 InputOption::VALUE_OPTIONAL,
                 'If "true", update the kernel, "false" if we should skip that.',
                 'true'
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
        parent::execute(
            $input,
            $output
        );

        $output->writeln(
            'Please review Resource/config/config.xml before commiting'
        );
    }

    /**
     * {@inheritDoc}
     * Add the new bundle to the BundleBundle loader infrastructure instead of main kernel
     *
     * @param DialogHelper    $dialog    dialog
     * @param InputInterface  $input     input
     * @param OutputInterface $output    output
     * @param KernelInterface $kernel    kernel
     * @param string          $namespace namespace
     * @param string          $bundle    bundle
     *
     * @return string[]
     */
    protected function updateKernel(
        QuestionHelper $questionHelper,
        InputInterface $input,
        OutputInterface $output,
        KernelInterface $kernel,
        $namespace,
        $bundle
    ) {

        // skip if kernel manipulation disabled by options (defaults to true)
        $doUpdate = $input->getOption('doUpdateKernel');
        if ($doUpdate == 'false') {
            return;
        }

        $auto = true;
        if ($input->isInteractive()) {
            $auto = $questionHelper->doAsk(
                $output,
                $questionHelper->getQuestion(
                    'Confirm automatic update of your core bundle',
                    'yes',
                    '?'
                ),
                true
            );
        }

        $output->write('Enabling the bundle inside the core bundle: ');
        $coreBundle = $kernel->getBundle($input->getOption('loaderBundleName'));
        if (!is_a(
            $coreBundle,
            '\Graviton\BundleBundle\GravitonBundleInterface'
        )
        ) {
            throw new \LogicException(
                'GravitonCoreBundle does not implement GravitonBundleInterface'
            );
        }
        $manip = new BundleBundleManipulator($coreBundle);
        try {
            $ret = $auto ? $manip->addBundle($namespace . '\\' . $bundle) : false;

            if (!$ret) {
                $reflected = new \ReflectionObject($kernel);

                return array(
                    sprintf(
                        '- Edit <comment>%s</comment>',
                        $reflected->getFilename()
                    ),
                    '  and add the following bundle in the <comment>GravitonCoreBundle::getBundles()</comment> method:',
                    '',
                    sprintf(
                        '    <comment>new %s(),</comment>',
                        $namespace . '\\' . $bundle
                    ),
                    ''
                );
            }
        } catch (\RuntimeException $e) {
            return array(
                sprintf(
                    'Bundle <comment>%s</comment> is already defined in <comment>%s)</comment>.',
                    $namespace . '\\' . $bundle,
                    'sGravitonCoreBundle::getBundles()'
                ),
                ''
            );
        }
    }

    /**
     * {@inheritDoc}
     * Don't check routing since graviton bundles usually get routed explicitly based on their naming.
     *
     * @param DialogHelper    $dialog dialog
     * @param InputInterface  $input  input
     * @param OutputInterface $output output
     * @param object          $bundle bundle
     * @param object          $format format
     *
     * @return string[]
     */
    protected function updateRouting(
        QuestionHelper $questionHelper,
        InputInterface $input,
        OutputInterface $output,
        $bundle,
        $format
    ) {
        return array();
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
