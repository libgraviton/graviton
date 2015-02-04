<?php

namespace Graviton\GeneratorBundle\Command;

use Graviton\GeneratorBundle\Generator\ResourceGenerator;
use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineEntityCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * generator command
 *
 * @category GeneratorBundle
 * @package  Graviton
 * @link     http://swisscom.ch
 */
class GenerateResourceCommand extends GenerateDoctrineEntityCommand
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
                'json',
                '',
                InputOption::VALUE_OPTIONAL,
                'Path to the json definition.'
            )
            ->addOption(
                'no-controller',
                '',
                InputOption::VALUE_OPTIONAL,
                'Pass if no controller should be generated'
            )
            ->setName('graviton:generate:resource')
            ->setDescription('Generates a graviton rest resource');
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
        // put input here for later use..
        $this->input = $input;

        parent::execute(
            $input,
            $output
        );

        $output->writeln(
            'For the time being you need to fix titles and add descriptions in Resource/config/schema manually'
        );
    }

    /**
     * {@inheritDoc}
     *
     * @return ResourceGenerator
     */
    protected function createGenerator()
    {
        return new ResourceGenerator(
            $this->input,
            $this->getContainer()->get('filesystem'),
            $this->getContainer()->get('doctrine'),
            $this->getContainer()->get('kernel')
        );
    }
}
