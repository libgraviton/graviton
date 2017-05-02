<?php
/**
 * cleans dynamic bundle directory
 */

namespace Graviton\GeneratorV2Bundle\Command;

use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Graviton\GeneratorBundle\Definition\Loader\Loader;
use Graviton\GeneratorBundle\Definition\Schema\Definition;
use Graviton\GeneratorBundle\Generator\ResourceGenerator\FieldMapper;
use Graviton\GeneratorV2Bundle\Service\SchemaMapper;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * php -d memory_limit=-1 vendor/graviton/graviton/app/console graviton:generate-v2:service-files
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class GenerateCommand extends Command
{

    /* @var \Graviton\AppKernel */
    private $kernel;

    /** @var Filesystem */
    private $filesystem;

    /** @var FieldMapper */
    private $mapper;

    /** @var Loader */
    private $loader;

    /** @var OutputInterface */
    private $output;

    /** @var string */
    private $contextMode;

    /** @var string */
    private $destinationDir;

    /** @var Definition */
    private $currentDefinition;

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('graviton:generate-v2:service-files')
            ->setDescription(
                'Will create the requires services definition files and routing'
            );
    }

    /**
     * set kernel
     *
     * @param mixed $kernel kernel
     *
     * @return void
     */
    public function setKernel($kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * set filesystem
     *
     * @param mixed $filesystem filesystem
     *
     * @return void
     */
    public function setFilesystem($filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritDoc}
     *
     * @param InputInterface $input input
     * @param OutputInterface $output output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->output->writeln('<info>');
        $this->output->writeln('Welcome to Service Generator v2.');
        $this->output->writeln('Hold on for a second while we fetch the definition files.');
        $this->output->writeln('</info>');

        /** @var Application $application */
        $application = $this->getApplication();
        /** @var ContainerInterface $container */
        $container = $application->getKernel()->getContainer();

        $this->loader = $container->get('graviton_generator.definition.loader');
        $this->mapper = $container->get('graviton_generator.resourcegenerator.field_mapper');

        // Get the base dir of the project to know if it's installed as vendor or stand alone app.
        $this->destinationDir = $container->getParameter('graviton.api.generated_service_directory');
        $this->contextMode = strpos($this->destinationDir, 'vendor/graviton/graviton') ? 'WRAPPER' : 'CORE';
        if ('WRAPPER' == $this->contextMode) {
            $this->destinationDir = str_replace('vendor/graviton/graviton/', '', $this->destinationDir);
        }

        $this->output->writeln(sprintf('Starting generation in %s context mode.', $this->contextMode));

        $this->generateServices($container->getParameter('kernel.root_dir'));

        $this->output->writeln('<info>');
        $this->output->writeln('Thanks you for using the service.');
        $this->output->writeln('Generated files are located in: ' . $this->destinationDir);
        $this->output->writeln('</info>');

    }

    /**
     * @param $rootDir
     */
    private function generateServices($rootDir)
    {
        $this->output->writeln('<info>Generating folder structure for services.</info>');
        $fileSystem = new Filesystem();
        if ($fileSystem->exists($this->destinationDir)) {
            $this->output->writeln('<comment> - > Removing old services.</comment>');
            $fileSystem->remove($this->destinationDir);
        }
        $fileSystem->mkdir($this->destinationDir);
        $fileSystem->mkdir($this->destinationDir . '/definition/');
        $fileSystem->mkdir($this->destinationDir . '/schema/');

        // Let get all definition files
        if ('CORE' == $this->contextMode) {
            $rootDir = realpath( $rootDir . '/../');
            $directories = [
                $rootDir . '/src/Graviton',
                $rootDir . '/vendor/libgraviton'
            ];
        } else {
            $rootDir = realpath($rootDir . '/../../../../vendor/');
            $directories = [
                $rootDir . '/graviton',
                $rootDir . '/grv',
            ];
        }
        $this->output->writeln('<info>Scanning for services in.</info>');
        foreach ($directories as $dir) {
            $this->output->writeln('<comment> - > '. $dir . '</comment>');
        }

        $finder = new Finder();
        $finder->files()->in($directories)
            ->name('*.json')
            ->notName('_*')
            ->path('/(^|\/)resources\/definition($|\/)/i');
        if ('WRAPPER' == $this->contextMode) {
            $finder->notPath('/(^|\/)Tests($|\/)/i');
        }

        $routes = [];

        $progress = new ProgressBar($this->output, $finder->count());

        foreach ($finder as $file) {
            $progress->advance();
            $path = $file->getRealPath();

            $json = json_decode($file->getContents());
            if (!$json || json_last_error()) {
                throw new InvalidConfigurationException('Failure: ' . $path . ': ' . json_last_error_msg());
            } elseif (!property_exists($json, 'id')) {
                throw new InvalidConfigurationException('Failure: ' . $path . ': id field is required');
            }

            // Routes
            if (property_exists($json, 'service' ) && property_exists($json->service, 'routerBase' )) {
                $routes[$json->id] = trim( $json->service->routerBase, "/" );
            }

            // Copy definition
            if (property_exists($json, 'service') && property_exists($json->service, 'fixtures')) {
                unset($json->service->fixtures);
            }
            $fileSystem->dumpFile($this->destinationDir . '/definition/' . $file->getFilename(), json_encode($json, JSON_PRETTY_PRINT));

            // Generate Schema
            $schema = $this->generateSchema($file);
            $fileSystem->dumpFile($this->destinationDir . '/schema/' . $file->getFilename(), $schema);
        }

        // Save routing
        $fileSystem->dumpFile($this->destinationDir . '/routes.json', json_encode($routes, JSON_PRETTY_PRINT));

    }


    /**
     * @param SplFileInfo $file
     * @return string
     */
    private function generateSchema($file)
    {
        $path = $file->getRealPath();

        /** @var JsonDefinition[] $definition */
        $definition = $this->loader->load($path);
        if (!array_key_exists(0, $definition) || !($definition[0] instanceof JsonDefinition)) {
            throw new InvalidConfigurationException('Failure: ' . $path . ': Incorrect JsonDefinition load.');
        }

        $this->currentDefinition = $definition[0];

        $mp = new SchemaMapper();
        $schema = $mp->convert($definition[0]);

        return json_encode($schema, JSON_PRETTY_PRINT);
    }
}
