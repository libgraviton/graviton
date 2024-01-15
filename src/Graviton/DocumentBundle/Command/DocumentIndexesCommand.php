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

        $this->manager->getSchemaManager()->ensureIndexes();

        return 0;
    }

    private function workOnClass(ClassMetadata $class)
    {
        if ($class->isMappedSuperclass || $class->isEmbeddedDocument || $class->isQueryResultDocument || $class->isView()) {
            return;
        }

        if ($class->getCollection() == "File") {
            $hans = 3;
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

    private function updateIndexesForClass(string $className)
    {
        try {
            $this->manager->getSchemaManager()
                ->ensureDocumentIndexes($className);
        } catch (CommandException $e) {
            // assume some name collision -> delete all indexes..
            $this->manager->getSchemaManager()->deleteDocumentIndexes($className);
        }
    }
}
