<?php
/**
 * graviton:mongodb:migrations:execute command
 */

namespace Graviton\MigrationBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Finder\Finder;
use Graviton\MigrationBundle\Command\Helper\DocumentManager as DocumentManagerHelper;
use AntiMattr\MongoDB\Migrations\OutputWriter;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class MongodbMigrateCommand extends Command
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Finder
     */
    private $finder;

    /**
     * @var DocumentManagerHelper
     */
    private $documentManager;

    /**
     * @var string
     */
    private $databaseName;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @param ContainerInterface    $container       container instance for injecting into aware migrations
     * @param Finder                $finder          finder that finds configs
     * @param DocumentManagerHelper $documentManager dm helper to get access to db in command
     * @param string                $databaseName    name of database where data is found in
     */
    public function __construct(
        ContainerInterface $container,
        Finder $finder,
        DocumentManagerHelper $documentManager,
        $databaseName
    ) {
        $this->container = $container;
        $this->finder = $finder;
        $this->documentManager = $documentManager;
        $this->databaseName = $databaseName;

        parent::__construct();
    }

    /**
     * setup command
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('graviton:mongodb:migrate');
    }

    /**
     * call execute on found commands
     *
     * @param InputInterface  $input  user input
     * @param OutputInterface $output command output
     *
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        // graviton root
        $baseDir = __DIR__.'/../../../';

        // vendorized? - go back some more..
        if (strpos($baseDir, '/vendor/') !== false) {
            $baseDir .= '../../../';
        }

        $this->finder
            ->in($baseDir)
            ->path('Resources/config')
            ->name('/migrations.(xml|yml)/')
            ->files();

        foreach ($this->finder as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $output->writeln('Found '.$file->getRelativePathname());

            $command = $this->getApplication()->find('mongodb:migrations:migrate');

            $helperSet = $command->getHelperSet();
            $helperSet->set($this->documentManager, 'dm');
            $command->setHelperSet($helperSet);

            $configuration = $this->getConfiguration($file->getPathname(), $output);
            self::injectContainerToMigrations($this->container, $configuration->getMigrations());
            $command->setMigrationConfiguration($configuration);

            $arguments = $input->getArguments();
            $arguments['command'] = 'mongodb:migrations:migrate';
            $arguments['--configuration'] = $file->getPathname();

            $migrateInput = new ArrayInput($arguments);
            $migrateInput->setInteractive($input->isInteractive());
            $returnCode = $command->run($migrateInput, $output);

            if ($returnCode !== 0) {
                $this->errors[] = sprintf(
                    'Calling mongodb:migrations:migrate failed for %s',
                    $file->getRelativePathname()
                );
            }
        }

        if (!empty($this->errors)) {
            $output->writeln(
                sprintf('<error>%s</error>', implode(PHP_EOL, $this->errors))
            );
            return -1;
        }

        return 0;
    }

    /**
     * get configration object for migration script
     *
     * This is based on antromattr/mongodb-migartion code but extends it so we can inject
     * non local stuff centrally.
     *
     * @param string $filepath path to configuration file
     * @param Output $output   ouput interface need by config parser to do stuff
     *
     * @return AntiMattr\MongoDB\Migrations\Configuration
     */
    private function getConfiguration($filepath, $output)
    {
        $outputWriter = new OutputWriter(
            function ($message) use ($output) {
                return $output->writeln($message);
            }
        );

        $info = pathinfo($filepath);
        $namespace = 'AntiMattr\MongoDB\Migrations\Configuration';
        $class = $info['extension'] === 'xml' ? 'XmlConfiguration' : 'YamlConfiguration';
        $class = sprintf('%s\%s', $namespace, $class);
        $configuration = new $class($this->documentManager->getDocumentManager()->getConnection(), $outputWriter);

        // register databsae name before loading to ensure that loading does not fail
        $configuration->setMigrationsDatabaseName($this->databaseName);

        // load additional config from migrations.(yml|xml)
        $configuration->load($filepath);

        return $configuration;
    }

    /**
     * Injects the container to migrations aware of it
     *
     * @param ContainerInterface $container container to inject into container aware migrations
     * @param array              $versions  versions that might need injecting a container
     *
     * @return void
     */
    private static function injectContainerToMigrations(ContainerInterface $container, array $versions)
    {
        foreach ($versions as $version) {
            $migration = $version->getMigration();
            if ($migration instanceof ContainerAwareInterface) {
                $migration->setContainer($container);
            }
        }
    }
}
