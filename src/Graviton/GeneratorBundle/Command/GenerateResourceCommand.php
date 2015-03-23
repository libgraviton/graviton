<?php
/**
 * generate:resource command
 */

namespace Graviton\GeneratorBundle\Command;

use Graviton\GeneratorBundle\Generator\ResourceGenerator;
use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineEntityCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * generator command
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class GenerateResourceCommand extends GenerateDoctrineEntityCommand
{
    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     *
     * @return ResourceGenerator
     */
    protected function createGenerator()
    {
        $generator = new ResourceGenerator(
            $this->getContainer()->get('filesystem'),
            $this->getContainer()->get('doctrine'),
            $this->getContainer()->get('kernel')
        );
        $generator->setNeedsController(
            $this->input->getOption('no-controller') != 'true'
        );
        if (!is_null($this->input->getOption('json'))) {
            $generator->setJson(new JsonDefinition($this->input->getOption('json')));
        }
        return $generator;
    }
}
