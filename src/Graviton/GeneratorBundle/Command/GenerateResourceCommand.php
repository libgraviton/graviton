<?php
/**
 * generate:resource command
 */

namespace Graviton\GeneratorBundle\Command;

use Graviton\GeneratorBundle\Generator\ResourceGenerator;
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
     * @var ResourceGenerator
     */
    private $resourceGenerator;

    /**
     * @param ResourceGenerator $resourceGenerator generator to use for resource generation
     */
    public function __construct(ResourceGenerator $resourceGenerator)
    {
        $this->resourceGenerator = $resourceGenerator;
        parent::__construct();
    }

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
        $this->resourceGenerator->setInput($this->input);
        return $this->resourceGenerator;
    }
}
