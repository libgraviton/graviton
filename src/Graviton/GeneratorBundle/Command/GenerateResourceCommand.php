<?php
/**
 * generate:resource command
 */

namespace Graviton\GeneratorBundle\Command;

use Graviton\GeneratorBundle\Generator\ResourceGenerator;
use Graviton\GeneratorBundle\Definition\Loader\LoaderInterface;
use Sensio\Bundle\GeneratorBundle\Command\GeneratorCommand;
use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * generator command
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class GenerateResourceCommand extends GeneratorCommand
{
    /**
     * @var Kernel
     */
    private $kernel;
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
     * @param Kernel            $kernel            kernel
     * @param ResourceGenerator $resourceGenerator generator to use for resource generation
     * @param LoaderInterface   $definitionLoader  JSON definition loaded
     */
    public function __construct(Kernel $kernel, ResourceGenerator $resourceGenerator, LoaderInterface $definitionLoader)
    {
        $this->kernel = $kernel;
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
        $this
            ->addOption(
                'entity',
                '',
                InputOption::VALUE_REQUIRED,
                'The entity name'
            )
            ->addOption(
                'json',
                '',
                InputOption::VALUE_REQUIRED,
                'The JSON Payload of the service'
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

        $entity = Validators::validateEntityName($input->getOption('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        $bundle = $this->kernel->getBundle($bundle);

        /** @var DoctrineEntityGenerator $generator */
        $generator = $this->getGenerator();
        $generatorResult = $generator->generate($bundle, $entity, 'xml', []);

        $output->writeln(
            sprintf(
                '> Generating entity class <info>%s</info>: <comment>OK!</comment>',
                $generatorResult->getEntityPath()
            )
        );
        $output->writeln(
            sprintf(
                '> Generating repository class <info>%s</info>: <comment>OK!</comment>',
                $generatorResult->getRepositoryPath()
            )
        );
        if ($generatorResult->getMappingPath()) {
            $output->writeln(
                sprintf(
                    '> Generating mapping file <info>%s</info>: <comment>OK!</comment>',
                    $generatorResult->getMappingPath()
                )
            );
        }
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

    /**
     * parses bundle shortcut notation
     *
     * @param string $shortcut shortcut
     *
     * @return array parsed name
     */
    protected function parseShortcutNotation($shortcut)
    {
        $entity = str_replace('/', '\\', $shortcut);

        if (false === $pos = strpos($entity, ':')) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The entity name must contain a : ("%s" given, expecting '.
                    'something like AcmeBlogBundle:Blog/Post)',
                    $entity
                )
            );
        }

        return array(substr($entity, 0, $pos), substr($entity, $pos + 1));
    }
}
