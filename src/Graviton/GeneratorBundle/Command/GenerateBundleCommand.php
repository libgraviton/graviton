<?php

namespace Graviton\GeneratorBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\GenerateBundleCommand as SymfonyGenerateBundleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Graviton\GeneratorBundle\Manipulator\BundleBundleManipulator;

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

        $this
            ->setName('graviton:generate:bundle')
            ->setDescription('Generates a graviton bundle');
    }

    /**
     * {@inheritDoc}
     *
     * Add the new bundle to the BundleBundle loader infrastructure instead of main kernel
     *
     * @return string[]
     */
    protected function updateKernel(DialogHelper $dialog, InputInterface $input, OutputInterface $output, KernelInterface $kernel, $namespace, $bundle)
    {
        $auto = true;
        if ($input->isInteractive()) {
            $auto = $dialog->askConfirmation($output, $dialog->getQuestion('Confirm automatic update of your core bundle', 'yes', '?'), true);
        }

        $output->write('Enabling the bundle inside the core bundle: ');
        $manip = new BundleBundleManipulator($kernel->getBundle('GravitonCoreBundle'));
        try {
            $ret = $auto ? $manip->addBundle($namespace.'\\'.$bundle) : false;

            if (!$ret) {
                $reflected = new \ReflectionObject($kernel);

                return array(
                    sprintf('- Edit <comment>%s</comment>', $reflected->getFilename()),
                    '  and add the following bundle in the <comment>GravitonCoreBundle::getBundles()</comment> method:',
                    '',
                    sprintf('    <comment>new %s(),</comment>', $namespace.'\\'.$bundle),
                    '',
                );
            }
        } catch (\RuntimeException $e) {
            return array(
                sprintf('Bundle <comment>%s</comment> is already defined in <comment>GravitonCoreBundle::getBundles()</comment>.', $namespace.'\\'.$bundle),
                '',
            );
        }
    }

    /**
     * {@inheritDoc}
     *
     * Don't check routing since graviton bundles usually get routed explicitly based on their naming.
     *
     * @return string[]
     */
    protected function updateRouting(DialogHelper $dialog, InputInterface $input, OutputInterface $output, $bundle, $format)
    {
        return array();
    }
}
