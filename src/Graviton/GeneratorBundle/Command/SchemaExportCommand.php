<?php
/**
 * generate MongoDB Fulltext-Search Indexes
 */

namespace Graviton\GeneratorBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;

/**
 * Here, we generate all MongoDB Fulltext-Search Indexes
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class SchemaExportCommand extends Command
{

    /**
     * @var documentManager
     */
    private $documentManager;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * GenerateBuildIndexesCommand constructor.
     *
     * @param DocumentManager $documentManager The Doctrine Document Manager
     * @param Filesystem      $fileSystem      Sf file manager
     * @param String          $name            The Name of this Command
     */
    public function __construct(
        DocumentManager $documentManager,
        Filesystem $fileSystem,
        $name = null
    ) {
        parent::__construct($name);

        $this->documentManager = $documentManager;
        $this->fileSystem = $fileSystem;
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
            ->setName('graviton:generate:schema-export')
            ->setDescription(
                'Generates Mongo DB schema output to root folder, schema.json'
            );
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
        // Check mongo db version
        $mongoVersion = $this->getMongoDBVersion('Graviton\\CoreBundle\\Document\\App');
        if ((float) $mongoVersion < 2.6) {
            $output->writeln("MongoDB Version < 2.6 installed: " . $mongoVersion);
            exit();
        }

        /** @var Application $application */
        $application = $this->getApplication();
        $container = $application->getKernel()->getContainer();
        $rootDir = $container->getParameter('kernel.root_dir');
        $baseDir = $rootDir . ((strpos($rootDir, 'vendor')) ? '/../../../../' : '/../');

        $schema = [];
        /** @var ClassMetadata $metadata */
        foreach ($this->documentManager->getMetadataFactory()->getAllMetadata() as $metadata) {
            $name = $metadata->getCollection();
            if (array_key_exists($name, $schema)) {
                throw new InvalidConfigurationException();
            }
            $schema[$name] = [
                'class'  => $metadata->getName(),
                'fields' => $metadata->fieldMappings,
                'relations' => $metadata->associationMappings
                ];
        }

        $this->fileSystem->dumpFile(
            $baseDir . '/schema.json',
            json_encode($schema, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Gets the installed MongoDB Version
     * @param String $className The Classname of the collection, needed to fetch the right DB connection
     * @return String getMongoDBVersion The version of the MongoDB as a string
     */
    private function getMongoDBVersion($className)
    {
        $buildInfo = $this->documentManager->getDocumentDatabase($className)->command(['buildinfo' => 1]);
        if (isset($buildInfo['version'])) {
            return $buildInfo['version'];
        } else {
            return 'unkown';
        }
    }
}
