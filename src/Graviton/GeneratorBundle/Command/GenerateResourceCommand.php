<?php
/**
 * generate:resource command
 */

namespace Graviton\GeneratorBundle\Command;

use Graviton\GeneratorBundle\Generator\ResourceGenerator;
use Graviton\GeneratorBundle\Definition\Loader\LoaderInterface;
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
     * @var LoaderInterface
     */
    private $definitionLoader;
    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @param ResourceGenerator $resourceGenerator generator to use for resource generation
     * @param LoaderInterface $definitionLoader JSON definition loaded
     */
    public function __construct(ResourceGenerator $resourceGenerator, LoaderInterface $definitionLoader)
    {
        $this->resourceGenerator = $resourceGenerator;
        $this->definitionLoader = $definitionLoader;
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
        // do we have a json path passed?
        if ($this->input->getOption('json') !== null) {
            $definitions = $this->definitionLoader->load($this->input->getOption('json'));
            if (count($definitions) > 0) {
                $this->resourceGenerator->setJson($definitions[0]);
            }
        }

        $this->resourceGenerator->setGenerateController(
            $this->input->getOption('no-controller') != 'true'
        );

        return $this->resourceGenerator;
    }
}
