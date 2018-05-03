<?php
/**
 * generate dynamic bundles
 */

namespace Graviton\GeneratorBundle\Command;

use Graviton\GeneratorBundle\CommandRunner;
use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Graviton\GeneratorBundle\Definition\JsonDefinitionArray;
use Graviton\GeneratorBundle\Definition\JsonDefinitionHash;
use Graviton\GeneratorBundle\Generator\BundleGenerator;
use Graviton\GeneratorBundle\Generator\DynamicBundleBundleGenerator;
use Graviton\GeneratorBundle\Definition\Loader\LoaderInterface;
use Graviton\GeneratorBundle\Generator\ResourceGenerator;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Here, we generate all "dynamic" Graviton bundles..
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class GenerateDynamicBundleCommand extends Command
{

    /** @var  string */
    const BUNDLE_NAMESPACE = 'GravitonDyn';

    /** @var  string */
    const BUNDLE_NAME_MASK = self::BUNDLE_NAMESPACE.'/%sBundle';

    /** @var  string */
    const GENERATION_HASHFILE_FILENAME = 'genhash';

    /** @var  string */
    private $bundleBundleNamespace;

    /** @var  string */
    private $bundleBundleDir;

    /** @var  string */
    private $bundleBundleClassname;

    /** @var  string */
    private $bundleBundleClassfile;

    /** @var  array */
    private $bundleBundleList = [];

    /** @var array|null */
    private $bundleAdditions = null;

    /** @var array|null */
    private $serviceWhitelist = null;
    /**
     * @var CommandRunner
     */
    private $runner;
    /**
     * @var LoaderInterface
     */
    private $definitionLoader;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var Filesystem
     */
    private $fs;
    /**
     * @var BundleGenerator
     */
    private $bundleGenerator;
    /**
     * @var ResourceGenerator
     */
    private $resourceGenerator;
    /**
     * @var DynamicBundleBundleGenerator
     */
    private $bundleBundleGenerator;

    /**
     * @param LoaderInterface              $definitionLoader      JSON definition loader
     * @param BundleGenerator              $bundleGenerator       bundle generator
     * @param ResourceGenerator            $resourceGenerator     resource generator
     * @param DynamicBundleBundleGenerator $bundleBundleGenerator bundlebundle generator
     * @param SerializerInterface          $serializer            Serializer
     * @param string|null                  $bundleAdditions       Additional bundles list in JSON format
     * @param string|null                  $serviceWhitelist      Service whitelist in JSON format
     * @param string|null                  $name                  name
     */
    public function __construct(
        LoaderInterface     $definitionLoader,
        BundleGenerator $bundleGenerator,
        ResourceGenerator $resourceGenerator,
        DynamicBundleBundleGenerator $bundleBundleGenerator,
        SerializerInterface $serializer,
        $bundleAdditions = null,
        $serviceWhitelist = null,
        $name = null
    ) {
        parent::__construct($name);

        $this->definitionLoader = $definitionLoader;
        $this->bundleGenerator = $bundleGenerator;
        $this->resourceGenerator = $resourceGenerator;
        $this->bundleBundleGenerator = $bundleBundleGenerator;
        $this->serializer = $serializer;
        $this->fs = new Filesystem();

        if ($bundleAdditions !== null && $bundleAdditions !== '') {
            $this->bundleAdditions = $bundleAdditions;
        }
        if ($serviceWhitelist !== null && $serviceWhitelist !== '') {
            $this->serviceWhitelist = $serviceWhitelist;
        }
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
            ->addOption(
                'srcDir',
                '',
                InputOption::VALUE_OPTIONAL,
                'Src Dir',
                dirname(__FILE__) . '/../../../'
            )
            ->addOption(
                'bundleBundleName',
                '',
                InputOption::VALUE_OPTIONAL,
                'Which BundleBundle to manipulate to add our stuff',
                'GravitonDynBundleBundle'
            )
            ->setName('graviton:generate:dynamicbundles')
            ->setDescription(
                'Generates all dynamic bundles in the GravitonDyn namespace. Either give a path '.
                'to a single JSON file or a directory path containing multiple files.'
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
        /**
         * GENERATE THE BUNDLEBUNDLE
         */
        $bundleBundleDir = sprintf(self::BUNDLE_NAME_MASK, 'Bundle');

        // GravitonDynBundleBundle
        $bundleName = str_replace('/', '', $bundleBundleDir);

        // bundlebundle stuff..
        $this->bundleBundleNamespace = $bundleBundleDir;
        $this->bundleBundleDir = $input->getOption('srcDir') . $bundleBundleDir;
        $this->bundleBundleClassname = $bundleName;
        $this->bundleBundleClassfile = $this->bundleBundleDir . '/' . $this->bundleBundleClassname . '.php';

        $filesToWorkOn = $this->definitionLoader->load($input->getOption('json'));

        if (count($filesToWorkOn) < 1) {
            throw new \LogicException("Could not find any usable JSON files.");
        }

        //$this->createInitialBundleBundle($input->getOption('srcDir'));

        $templateHash = $this->getTemplateHash();
        $existingBundles = $this->getExistingBundleHashes($input->getOption('srcDir'));

        /**
         * GENERATE THE BUNDLE(S)
         */
        foreach ($filesToWorkOn as $jsonDef) {
            $thisIdName = $jsonDef->getId();
            $namespace = sprintf(self::BUNDLE_NAME_MASK, $thisIdName);

            // make sure bundle is in bundlebundle
            $this->bundleBundleList[] = $namespace;

            $jsonDef->setNamespace($namespace);

            $bundleName = str_replace('/', '', $namespace);
            $bundleDir = $input->getOption('srcDir').$namespace;
            $bundleNamespace = str_replace('/', '\\', $namespace).'\\';

            try {
                $thisHash = sha1($templateHash.PATH_SEPARATOR.serialize($jsonDef));

                $needsGeneration = true;
                if (isset($existingBundles[$bundleDir])) {
                    if ($existingBundles[$bundleDir] == $thisHash) {
                        $needsGeneration = false;
                    }
                    unset($existingBundles[$bundleDir]);
                }

                if ($needsGeneration) {
                    $this->generateBundle($bundleNamespace, $bundleName, $input->getOption('srcDir'));
                    $this->generateGenerationHashFile($bundleDir, $thisHash);
                }

                if ($needsGeneration) {
                    $this->generateResources(
                        $jsonDef,
                        $bundleName,
                        $bundleDir,
                        $bundleNamespace
                    );

                    $output->write(
                        PHP_EOL.
                        sprintf('<info>Generated "%s" from definition %s</info>', $bundleName, $jsonDef->getId()).
                        PHP_EOL
                    );
                } else {
                    $output->write(
                        PHP_EOL.
                        sprintf('<info>Using pre-existing "%s"</info>', $bundleName).
                        PHP_EOL
                    );
                }
            } catch (\Exception $e) {
                $output->writeln(
                    sprintf('<error>%s</error>', $e->getMessage())
                );

                // remove failed bundle from list
                array_pop($this->bundleBundleList);
            }
        }

        // whatever is left in $existingBundles is not defined anymore and needs to be deleted..
        foreach ($existingBundles as $dirName => $hash) {
            $fileInfo = new \SplFileInfo($dirName);
            $bundleClassName = $this->getBundleClassnameFromFolder($fileInfo->getFilename());

            // remove from bundlebundle list
            unset($this->bundleBundleList[array_search($bundleClassName, $this->bundleBundleList)]);

            $this->fs->remove($dirName);

            $output->write(
                PHP_EOL.
                sprintf('<info>Deleted obsolete bundle "%s"</info>', $dirName).
                PHP_EOL
            );
        }

        // generate bundlebundle
        $this->generateBundleBundleClass();
    }

    /**
     * scans through all existing dynamic bundles, checks if there is a generation hash and collect that
     * all in an array that can be used for fast checking.
     *
     * @param string $baseDir base directory of dynamic bundles
     *
     * @return array key is bundlepath, value is the current hash
     */
    private function getExistingBundleHashes($baseDir)
    {
        $existingBundles = [];
        $fs = new Filesystem();
        $bundleBaseDir = $baseDir.self::BUNDLE_NAMESPACE;

        if (!$fs->exists($bundleBaseDir)) {
            return $existingBundles;
        }

        $bundleFinder = $this->getBundleFinder($baseDir);

        foreach ($bundleFinder as $bundleDir) {
            $genHash = '';
            $hashFileFinder = new Finder();
            $hashFileIterator = $hashFileFinder
                ->files()
                ->in($bundleDir->getPathname())
                ->name(self::GENERATION_HASHFILE_FILENAME)
                ->depth('== 0')
                ->getIterator();

            $hashFileIterator->rewind();

            $hashFile = $hashFileIterator->current();
            if ($hashFile instanceof SplFileInfo) {
                $genHash = $hashFile->getContents();
            }

            $existingBundles[$bundleDir->getPathname()] = $genHash;
        }

        return $existingBundles;
    }

    /**
     * from a name of a folder of a bundle, this function returns the corresponding class name
     *
     * @param string $folderName folder name
     *
     * @return string
     */
    private function getBundleClassnameFromFolder($folderName)
    {
        if (substr($folderName, -6) == 'Bundle') {
            $folderName = substr($folderName, 0, -6);
        }

        return sprintf(self::BUNDLE_NAME_MASK, $folderName);
    }

    /**
     * returns a finder that iterates all bundle directories
     *
     * @param string $baseDir the base dir to search
     *
     * @return Finder|null finder or null if basedir does not exist
     */
    private function getBundleFinder($baseDir)
    {
        $bundleBaseDir = $baseDir.self::BUNDLE_NAMESPACE;

        if (!(new Filesystem())->exists($bundleBaseDir)) {
            return null;
        }

        $bundleFinder = new Finder();
        $bundleFinder->directories()->in($bundleBaseDir)->depth('== 0')->notName('BundleBundle');

        return $bundleFinder;
    }

    /**
     * Calculates a hash of all templates that generator uses to output it's file.
     * That way a regeneration will be triggered when one of them changes..
     *
     * @return string hash
     */
    private function getTemplateHash()
    {
        $templateDir = __DIR__ . '/../Resources/skeleton';
        $resourceFinder = new Finder();
        $resourceFinder->in($templateDir)->files()->sortByName();
        $templateTimes = '';
        foreach ($resourceFinder as $file) {
            $templateTimes .= PATH_SEPARATOR . sha1_file($file->getPathname());
        }
        return sha1($templateTimes);
    }

    /**
     * Generate Bundle entities
     *
     * @param OutputInterface $output          Instance to sent text to be displayed on stout.
     * @param JsonDefinition  $jsonDef         Configuration to be generated the entity from.
     * @param string          $bundleName      Name of the bundle the entity shall be generated for.
     * @param string          $bundleClassName class name
     *
     * @return void
     * @throws \Exception
     */
    protected function generateSubResources(
        OutputInterface $output,
        JsonDefinition $jsonDef,
        $bundleName,
        $bundleClassName
    ) {
        foreach ($this->getSubResources($jsonDef) as $subRecource) {
            $arguments = [
                'graviton:generate:resource',
                '--no-debug' => null,
                '--entity' => $bundleName . ':' . $subRecource->getId(),
                '--bundleClassName' => $bundleClassName,
                '--json' => $this->serializer->serialize($subRecource->getDef(), 'json'),
                '--no-controller' => 'true',
            ];
            $this->generateResource($arguments, $output, $jsonDef);
        }
    }

    /**
     * generates the resources of a bundle
     *
     * @param JsonDefinition $jsonDef         definition
     * @param string         $bundleName      name
     * @param string         $bundleDir       dir
     * @param string         $bundleNamespace namespace
     *
     * @return void
     */
    protected function generateResources(
        JsonDefinition $jsonDef,
        $bundleName,
        $bundleDir,
        $bundleNamespace
    ) {

        /** @var ResourceGenerator $generator */
        $generator = $this->resourceGenerator;
        $generator->setGenerateController(false);

        foreach ($this->getSubResources($jsonDef) as $subRecource) {
            $generator->setJson(new JsonDefinition($subRecource->getDef()->setIsSubDocument(true)));
            $generator->generate(
                $bundleDir,
                $bundleNamespace,
                $bundleName,
                $subRecource->getId()
            );
        }

        // main resources
        if (!empty($jsonDef->getFields())) {
            $generator->setGenerateController(true);
            $generator->setJson(new JsonDefinition($jsonDef->getDef()));
            $generator->generate(
                $bundleDir,
                $bundleNamespace,
                $bundleName,
                $jsonDef->getId()
            );
        }
    }

    /**
     * Generate the actual Bundle
     *
     * @param OutputInterface $output          Instance to sent text to be displayed on stout.
     * @param JsonDefinition  $jsonDef         Configuration to be generated the entity from.
     * @param string          $bundleName      Name of the bundle the entity shall be generated for.
     * @param string          $bundleClassName class name for bundle
     *
     * @return void
     */
    protected function generateMainResource(
        OutputInterface $output,
        JsonDefinition $jsonDef,
        $bundleName,
        $bundleClassName
    ) {
        if (!empty($jsonDef->getFields())) {
            $arguments = array(
                'graviton:generate:resource',
                '--no-debug' => null,
                '--entity' => $bundleName . ':' . $jsonDef->getId(),
                '--bundleClassName' => $bundleClassName,
                '--json' => $this->serializer->serialize($jsonDef->getDef(), 'json')
            );

            $this->generateResource($arguments, $output, $jsonDef);
        }
    }

    /**
     * Get all sub hashes
     *
     * @param JsonDefinition $definition Main JSON definition
     * @return JsonDefinition[]
     */
    protected function getSubResources(JsonDefinition $definition)
    {
        $resources = [];
        foreach ($definition->getFields() as $field) {
            while ($field instanceof JsonDefinitionArray) {
                $field = $field->getElement();
            }
            if (!$field instanceof JsonDefinitionHash) {
                continue;
            }

            $subDefiniton = $field->getJsonDefinition();

            $resources = array_merge($this->getSubResources($subDefiniton), $resources);
            $resources[] = $subDefiniton;
        }

        return $resources;
    }

    /**
     * Gathers data for the command to run.
     *
     * @param array           $arguments Set of cli arguments passed to the command
     * @param OutputInterface $output    Output channel to send messages to.
     * @param JsonDefinition  $jsonDef   Configuration of the service
     *
     * @return void
     * @throws \LogicException
     */
    private function generateResource(array $arguments, OutputInterface $output, JsonDefinition $jsonDef)
    {
        // controller?
        $routerBase = $jsonDef->getRouterBase();
        if ($routerBase === false || $this->isNotWhitelistedController($routerBase)) {
            $arguments['--no-controller'] = 'true';
        }

        $this->runner->executeCommand($arguments, $output, 'Create resource call failed, see above. Exiting.');
    }

    /**
     * generates the basic bundle structure
     *
     * @param string $namespace  Namespace
     * @param string $bundleName Name of bundle
     * @param string $targetDir  target directory
     *
     * @return void
     *
     * @throws \LogicException
     */
    private function generateBundle(
        $namespace,
        $bundleName,
        $targetDir
    ) {
        $this->bundleGenerator->generate(
            $namespace,
            $bundleName,
            $targetDir,
            'yml'
        );
    }

    /**
     * Generates our BundleBundle for dynamic bundles.
     * It basically replaces the Bundle main class that got generated
     * by the Sensio bundle task and it includes all of our bundles there.
     *
     * @return void
     */
    private function generateBundleBundleClass()
    {
        // add optional bundles if defined by parameter.
        if ($this->bundleAdditions !== null) {
            $this->bundleBundleGenerator->setAdditions($this->bundleAdditions);
        } else {
            $this->bundleBundleGenerator->setAdditions([]);
        }

        $this->bundleBundleGenerator->generate(
            $this->bundleBundleList,
            $this->bundleBundleNamespace,
            $this->bundleBundleClassname,
            $this->bundleBundleClassfile
        );
    }

    /**
     * Checks an optional environment setting if this $routerBase is whitelisted there.
     * If something is 'not whitelisted' (return true) means that the controller should not be generated.
     * This serves as a lowlevel possibility to disable the generation of certain controllers.
     * If we have no whitelist defined, we consider that all services should be generated (default).
     *
     * @param string $routerBase router base
     *
     * @return bool true if yes, false if not
     */
    private function isNotWhitelistedController($routerBase)
    {
        if ($this->serviceWhitelist === null) {
            return false;
        }

        return !in_array($routerBase, $this->serviceWhitelist, true);
    }

    /**
     * Generates the file containing the hash to determine if this bundle needs regeneration
     *
     * @param string $bundleDir directory of the bundle
     * @param string $hash      the hash to save
     *
     * @return void
     */
    private function generateGenerationHashFile($bundleDir, $hash)
    {
        $fs = new Filesystem();
        if ($fs->exists($bundleDir)) {
            $fs->dumpFile($bundleDir.DIRECTORY_SEPARATOR.self::GENERATION_HASHFILE_FILENAME, $hash);
        }
    }
}
