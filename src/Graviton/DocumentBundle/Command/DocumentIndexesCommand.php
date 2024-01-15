<?php
/**
 * generate indexes
 */

namespace Graviton\DocumentBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Graviton\GeneratorBundle\Definition\Loader\LoaderInterface;
use Graviton\GeneratorBundle\Generator\BundleGenerator;
use Graviton\GeneratorBundle\Generator\DynamicBundleBundleGenerator;
use Graviton\GeneratorBundle\Generator\ResourceGenerator;
use JMS\Serializer\SerializerInterface;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Driver\Exception\CommandException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Here, we generate all "dynamic" Graviton bundles..
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class DocumentIndexesCommand extends Command
{

    private DocumentManager $manager;
    private array $usedClasses = [];
    private array $documentIndexes = [];

    /**
     * @var array we only want to process documents that are referenced! not embeds!
     */
    private array $relevantAssociations = [ClassMetadata::REFERENCE_ONE, ClassMetadata::REFERENCE_MANY];

    /**
     * @param LoaderInterface              $definitionLoader      JSON definition loader
     * @param BundleGenerator              $bundleGenerator       bundle generator
     * @param ResourceGenerator            $resourceGenerator     resource generator
     * @param DynamicBundleBundleGenerator $bundleBundleGenerator bundlebundle generator
     * @param SerializerInterface          $serializer            Serializer
     * @param string|null                  $bundleAdditions       Additional bundles list in JSON format
     * @param string|null                  $serviceWhitelist      Service whitelist in JSON format
     * @param string|null                  $name                  name
     * @param string|null                  $syntheticFields       comma separated list of synthetic fields to create
     * @param string|null                  $ensureIndexes         comma separated list of indexes to ensure
     */
    public function __construct(
        DocumentManager $manager
    ) {
        parent::__construct('graviton:ensure-indexes');
        $this->manager = $manager;
    }

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();
    }

    /**
     * {@inheritDoc}
     *
     * @param InputInterface  $input  input
     * @param OutputInterface $output output
     *
     * @return int exit code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->manager->getMetadataFactory()->getAllMetadata() as $class) {
            $this->workOnClass($class);
        }

        $client = $this->manager->getClient();

        // which database?
        $dbs = $this->manager->getDocumentDatabases();
        if (count($dbs) < 1) {
            throw new \LogicException("Could not determine database!");
        }
        $db = array_pop($dbs);
        $dbName = $db->getDatabaseName();

        // delete unused collections!
        $this->deleteUnusedCollections($client, $dbName);

        // ensure indexes
        $this->ensureIndexes($client, $dbName);

        return Command::SUCCESS;
    }

    private function deleteUnusedCollections(Client $client, string $dbName) {
        $wantToKeep = array_values($this->usedClasses);
        $db = $client->selectDatabase($dbName);

        foreach ($db->listCollectionNames() as $collectionName) {
            if (!in_array($collectionName, $wantToKeep)) {
                // recordcount?
                $collection = $db->selectCollection($collectionName);
                if ($collection->countDocuments() < 1) {
                    $db->dropCollection($collectionName);
                }
            }

        }
    }

    private function ensureIndexes(Client $client, string $dbName) {
        foreach ($this->usedClasses as $className => $collectionName) {
            // has indexes?
            if (empty($this->documentIndexes[$collectionName])) {
                continue;
            }

            $this->manager->clear();

            $collection = $client->selectDatabase($dbName)->selectCollection($collectionName);

            $hasIndexes = $this->manager->getSchemaManager()->getDocumentIndexes($className);
            $hasIndexes2 = $this->getCurrentIndexes($collection);
            $shouldIndexes = $this->documentIndexes[$collectionName];

            if ($collection->getCollectionName() == 'AccountStatement') {
                $hans = 3;
            }

            // first, delete unnecessary ones
            $this->diffAndDoSomething($collection, $shouldIndexes, $hasIndexes,'deleteRight');

            // first, diff *has* and *should* and create right missing
            $this->diffAndDoSomething($collection, $hasIndexes, $shouldIndexes, 'createRight');
        }

    }

    /**
     * diff left and right index and do something
     *
     * @param $leftSide
     * @param $rightSide
     * @return void
     */
    private function diffAndDoSomething(Collection $collection, $leftSide, $rightSide, $action)
    {


        foreach ($rightSide as $rightIndex) {


            // this side options
            $rightKeys = array($rightIndex['keys'])[0];
            $rightOptions = array($rightIndex['options'])[0];

            if ($collection->getCollectionName() == 'AccountStatement' && $rightKeys == ['account.$id' => 1]) {
                $hans = 3;
            }

            unset($rightOptions['name']);

            // do we have this?
            $doesRightExist = false;
            foreach ($leftSide as $leftIndex) {
                $leftKeys = $leftIndex['keys'];
                $leftOptions = $leftIndex['options'];
                unset($leftOptions['name']);

                $doesRightExist = empty(array_diff_assoc($leftKeys, $rightKeys)) && empty(array_diff_assoc($leftOptions, $rightOptions));

                if ($doesRightExist) {
                    // break if found!
                    break;
                }
            }

            if (!$doesRightExist && $action == 'deleteRight') {
                try {
                    $collection->dropIndex($rightIndex['options']['name']);
                } catch (\Throwable $t) {
                    $hans = 3;
                }
            }

            if (!$doesRightExist && $action == 'createRight') {
                try {
                    $collection->createIndex($rightIndex['keys'], $rightIndex['options']);
                } catch (\Throwable $t) {
                    $hans = 3;
                }
            }


        }


    }

    private function getCurrentIndexes(Collection $collection)
    {
        $indexes = [];
        foreach ($collection->listIndexes() as $idx) {

            $hans = 3;
        }
        $indexes = iterator_to_array($collection->listIndexes());
        $hans = 3;
    }

    private function workOnClass(ClassMetadata $class)
    {
        if ($class->isMappedSuperclass || $class->isEmbeddedDocument || $class->isQueryResultDocument || $class->isView()) {
            return;
        }

        $this->usedClasses[$class->getName()] = $class->getCollection();
        $this->documentIndexes[$class->getCollection()] = $class->getIndexes();

        // iterate fields
        foreach ($class->getFieldNames() as $fieldName) {
            $mapping = $class->getFieldMapping($fieldName);
            if ($class->hasAssociation($fieldName) && in_array($mapping['association'], $this->relevantAssociations)) {
                $targetClass = $class->getAssociationTargetClass($fieldName);
                $this->workOnClass($this->manager->getMetadataFactory()->getMetadataFor($targetClass));
            }
        }
    }
}
