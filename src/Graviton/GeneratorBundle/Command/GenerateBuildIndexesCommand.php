<?php
/**
 * generate MongoDB Fulltext-Search Indexes
 */

namespace Graviton\GeneratorBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Graviton\GeneratorBundle\Definition\Loader\LoaderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Here, we generate all MongoDB Fulltext-Search Indexes
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class GenerateBuildIndexesCommand extends Command
{
    /**
     * @var LoaderInterface
     */
    private $definitionLoader;

    /**
     * @var documentManager
     */
    private $documentManager;

    /**
     * GenerateBuildIndexesCommand constructor.
     *
     * @param LoaderInterface $definitionLoader The definition Loader - loads Definitions from JSON-Files
     * @param DocumentManager $documentManager  The Doctrine Document Manager
     * @param String          $name             The Name of this Command
     */
    public function __construct(
        LoaderInterface $definitionLoader,
        DocumentManager $documentManager,
        $name = null
    ) {
        parent::__construct($name);

        $this->definitionLoader = $definitionLoader;
        $this->documentManager = $documentManager;
    }

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this->addOption(
            'json',
            '',
            InputOption::VALUE_OPTIONAL,
            'Path to the json definition.'
        )
            ->setName('graviton:generate:build-indexes')
            ->setDescription(
                'Generates Mongo-Text Indexes (MongoDB >= 2.6) for collections as defined'
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
        $filesToWorkOn = $this->definitionLoader->load($input->getOption('json'));
        if (count($filesToWorkOn) < 1) {
            throw new \LogicException('Could not find any usable JSON files.');
        }

        /**
         * Generate Indexes, if definition is found.
         */
        foreach ($filesToWorkOn as $jsonDef) {
            $textSearchIndexDefinitionFromJson = $this->getTextSearchIndexDefinitionFromJson($jsonDef);
            if (count($textSearchIndexDefinitionFromJson)) {
                $className = 'GravitonDyn\\' . $jsonDef->getId() . 'Bundle\\Document\\' . $jsonDef->getId();
                // Check MongoVersion, unfortunately needs Classname to fetch the right DB Connection
                $mongoVersion = $this->getMongoDBVersion($className);
                if ((float) $mongoVersion >= 2.6) {
                    $indexName = $textSearchIndexDefinitionFromJson[1]['name'];
                    $collection = $this->documentManager->getDocumentCollection($className);
                    $collection->deleteIndex();
                    $collection->ensureIndex(
                        $textSearchIndexDefinitionFromJson[0],
                        $textSearchIndexDefinitionFromJson[1]
                    );
                    echo "Created index '" . $indexName . "' for Collection '" . $collection->getName() . "'\n";
                } else {
                    echo "Couldn't create text Index for Collection " . $className
                        . ". MongoDB Version =< 2.6 installed: " . $mongoVersion . "\n";
                }
            }
        }
    }

    /**
     * @param JsonDefinition $jsonDef the Json-Definition-Object for a Service/Collection
     * @return array the Index-Definition-Array
     */
    private function getTextSearchIndexDefinitionFromJson(JsonDefinition $jsonDef)
    {
        $index = [];
        foreach ($jsonDef->getFields() as $field) {
            if (isset($field->getDefAsArray()['searchable']) && $field->getDefAsArray()['searchable'] > 0) {
                $index[0][$field->getName()] = 'text';
                $index[1]['weights'][$field->getName()] = (int) $field->getDefAsArray()['searchable'];
            }
        };
        if (isset($index[1])) {
            $index[1]['name'] = 'search' . $jsonDef->getId();
            $index[1]['default_language'] = 'de';
            $index[1]['language_override'] = 'language';
        }
        return $index;
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
