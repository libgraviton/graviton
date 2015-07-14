<?php
/**
 * generate dynamic bundles
 */

namespace Graviton\GeneratorBundle\Command;

use Graviton\GeneratorBundle\CommandRunner;
use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Graviton\GeneratorBundle\Definition\JsonDefinitionHash;
use Graviton\GeneratorBundle\Generator\DynamicBundleBundleGenerator;
use Graviton\GeneratorBundle\Generator\ResourceGenerator;
use Graviton\GeneratorBundle\Manipulator\File\XmlManipulator;
use Graviton\GeneratorBundle\Definition\Loader\LoaderInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Here, we generate all "dynamic" Graviton bundles..
 *
 * @todo     create a new Application in-situ
 * @todo     see if we can get rid of container dependency..
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class GenerateDynamicBundleCommand extends Command
{
    const BUNDLE_NAME_MASK = 'GravitonDyn/%sBundle';

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
     * @var XmlManipulator
     */
    private $xmlManipulator;
    /**
     * @var SerializerInterface
     */
    private $serializer;


    /**
     * @param CommandRunner       $runner           Runs a console command.
     * @param XmlManipulator      $xmlManipulator   Helper to change the content of a xml file.
     * @param LoaderInterface     $definitionLoader JSON definition loader
     * @param SerializerInterface $serializer       Serializer
     * @param string|null         $bundleAdditions  Additional bundles list in JSON format
     * @param string|null         $serviceWhitelist Service whitelist in JSON format
     * @param string|null         $name             The name of the command; passing null means it must be set in
     *                                              configure()
     */
    public function __construct(
        CommandRunner       $runner,
        XmlManipulator      $xmlManipulator,
        LoaderInterface     $definitionLoader,
        SerializerInterface $serializer,
        $bundleAdditions = null,
        $serviceWhitelist = null,
        $name = null
    ) {
        parent::__construct($name);

        $this->runner = $runner;
        $this->xmlManipulator = $xmlManipulator;
        $this->definitionLoader = $definitionLoader;
        $this->serializer = $serializer;

        $this->bundleAdditions = $bundleAdditions === null ? null : json_decode($bundleAdditions, true);
        $this->serviceWhitelist = $serviceWhitelist === null ? null : json_decode($serviceWhitelist, true);
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
            ->addOption(
                'bundleFormat',
                '',
                InputOption::VALUE_OPTIONAL,
                'Which format',
                'xml'
            )
            ->setName('graviton:generate:dynamicbundles')
            ->setDescription(
                'Generates all dynamic bundles in the GravitonDyn namespace. Either give a path
                    to a single JSON file or a directory path containing multiple files.'
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
        $namespace = sprintf(self::BUNDLE_NAME_MASK, 'Bundle');

        // GravitonDynBundleBundle
        $bundleName = str_replace('/', '', $namespace);

        // bundlebundle stuff..
        $this->bundleBundleNamespace = $namespace;
        $this->bundleBundleDir = $input->getOption('srcDir') . $namespace;
        $this->bundleBundleClassname = $bundleName;
        $this->bundleBundleClassfile = $this->bundleBundleDir . '/' . $this->bundleBundleClassname . '.php';

        $filesToWorkOn = $this->definitionLoader->load($input->getOption('json'));

        if (count($filesToWorkOn) < 1) {
            throw new \LogicException("Could not find any usable JSON files.");
        }

        /**
         * GENERATE THE BUNDLE(S)
         */
        foreach ($filesToWorkOn as $jsonDef) {
            $thisIdName = $jsonDef->getId();
            $namespace = sprintf(self::BUNDLE_NAME_MASK, $thisIdName);

            $jsonDef->setNamespace($namespace);

            $bundleName = str_replace('/', '', $namespace);
            $this->bundleBundleList[] = $namespace;

            try {
                $this->generateBundle($namespace, $bundleName, $input, $output);
                $this->generateBundleBundleClass($this->bundleAdditions);
                $this->generateSubResources($output, $jsonDef, $this->xmlManipulator, $bundleName, $namespace);
                $this->generateMainResource($output, $jsonDef, $bundleName);
                $this->generateValidationXml($this->xmlManipulator, $this->getGeneratedValidationXmlPath($namespace));

                $output->writeln('');
                $output->writeln(
                    sprintf('<info>Generated "%s" from definition %s</info>', $bundleName, $jsonDef->getId())
                );
                $output->writeln('');
            } catch (\Exception $e) {
                $output->writeln(
                    sprintf('<error>%s</error>', $e->getMessage())
                );

                // remove failed bundle from list
                array_pop($this->bundleBundleList);
            }

            $this->xmlManipulator->reset();
        }
    }

    /**
     * Generate Bundle entities
     *
     * @param OutputInterface $output         Instance to sent text to be displayed on stout.
     * @param JsonDefinition  $jsonDef        Configuration to be generated the entity from.
     * @param XmlManipulator  $xmlManipulator Helper to safe the validation xml file.
     * @param string          $bundleName     Name of the bundle the entity shall be generated for.
     * @param string          $namespace      Absolute path to the bundle root dir.
     *
     * @return void
     * @throws \Exception
     */
    protected function generateSubResources(
        OutputInterface $output,
        JsonDefinition $jsonDef,
        XmlManipulator $xmlManipulator,
        $bundleName,
        $namespace
    ) {
        foreach ($jsonDef->getFields() as $field) {
            if ($field instanceof JsonDefinitionHash && !$field->isBagOfPrimitives()) {
                $hashDefinition = $field->getDefFromLocal();

                $arguments = array(
                    'graviton:generate:resource',
                    '--entity' => $bundleName . ':' . $field->getClassName(),
                    '--format' => 'xml',
                    '--json' => $this->serializer->serialize($hashDefinition->getDef(), 'json'),
                    '--fields' => $this->getFieldString($hashDefinition),
                    '--with-repository' => null,
                    '--no-controller' => 'true'
                );
                $this->generateResource($arguments, $output, $jsonDef);

                // look for validation.xml and save it from over-writing ;-)
                // we basically get the xml content that was generated in order to save them later..
                $validationXml = $this->getGeneratedValidationXmlPath($namespace);
                if (file_exists($validationXml)) {
                    $xmlManipulator->addNodes(file_get_contents($validationXml));
                }
            }
        }
    }

    /**
     * Generate the actual Bundle
     *
     * @param OutputInterface $output     Instance to sent text to be displayed on stout.
     * @param JsonDefinition  $jsonDef    Configuration to be generated the entity from.
     * @param string          $bundleName Name of the bundle the entity shall be generated for.
     *
     * @return void
     */
    protected function generateMainResource(OutputInterface $output, JsonDefinition $jsonDef, $bundleName)
    {
        $fields = $jsonDef->getFields();
        if (!empty($fields)) {
            $arguments = array(
                'graviton:generate:resource',
                '--entity' => $bundleName . ':' . $jsonDef->getId(),
                '--json' => $this->serializer->serialize($jsonDef->getDef(), 'json'),
                '--format' => 'xml',
                '--fields' => $this->getFieldString($jsonDef),
                '--with-repository' => null
            );

            $this->generateResource($arguments, $output, $jsonDef);
        }
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
        if (!$jsonDef->hasController() || $this->isNotWhitelistedController($jsonDef->getRouterBase())) {
            $arguments['--no-controller'] = 'true';
        }

        $this->runner->executeCommand($arguments, $output, 'Create resource call failed, see above. Exiting.');
    }

    /**
     * Generates a Bundle via command line (wrapping graviton:generate:bundle)
     *
     * @param string          $namespace  Namespace
     * @param string          $bundleName Name of bundle
     * @param InputInterface  $input      Input
     * @param OutputInterface $output     Output
     *
     * @return void
     *
     * @throws \LogicException
     */
    private function generateBundle(
        $namespace,
        $bundleName,
        InputInterface $input,
        OutputInterface $output
    ) {
        // first, create the bundle
        $arguments = array(
            'graviton:generate:bundle',
            '--namespace' => $namespace,
            '--bundle-name' => $bundleName,
            '--dir' => $input->getOption('srcDir'),
            '--format' => $input->getOption('bundleFormat'),
            '--doUpdateKernel' => 'false',
            '--loaderBundleName' => $input->getOption('bundleBundleName'),
            '--structure' => null
        );

        $this->runner->executeCommand(
            $arguments,
            $output,
            'Create bundle call failed, see above. Exiting.'
        );
    }

    /**
     * Generates our BundleBundle for dynamic bundles.
     * It basically replaces the Bundle main class that got generated
     * by the Sensio bundle task and it includes all of our bundles there.
     *
     * @param array $additions List of additional bundles
     *
     * @return void
     */
    private function generateBundleBundleClass($additions)
    {
        $dbbGenerator = new DynamicBundleBundleGenerator();

        // add optional bundles if defined by parameter.
        if (!empty($additions) && is_array($additions)) {
            $dbbGenerator->setAdditions($additions);
        }

        $dbbGenerator->generate(
            $this->bundleBundleList,
            $this->bundleBundleNamespace,
            $this->bundleBundleClassname,
            $this->bundleBundleClassfile
        );
    }

    /**
     * Returns the path to the generated validation.xml
     *
     * @param string $namespace Namespace
     *
     * @return string path
     */
    private function getGeneratedValidationXmlPath($namespace)
    {
        return dirname(__FILE__) . '/../../../' . $namespace . '/Resources/config/validation.xml';
    }

    /**
     * Returns the field string as described in the json file
     *
     * @param JsonDefinition $jsonDef The json def
     *
     * @return string CommandLine string for the generator command
     */
    private function getFieldString(JsonDefinition $jsonDef)
    {
        $ret = array();

        foreach ($jsonDef->getFields() as $field) {
            // don't add 'id' field it seems..
            if ($field->getName() != 'id') {
                $ret[] = $field->getName() . ':' . $field->getTypeDoctrine();
            }
        }

        return implode(
            ' ',
            $ret
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
        if (!is_array($this->serviceWhitelist)) {
            return false;
        }

        return !in_array($routerBase, $this->serviceWhitelist, true);
    }

    /**
     * renders and stores the validation.xml file of a bundle.
     *
     * what are we doing here?
     * well, when we started to generate our subclasses (hashes in our own service) as own
     * Document classes, i had the problem that the validation.xml always got overwritten by the
     * console task. sadly, validation.xml is one file for all classes in the bundle.
     * so here we merge the generated validation.xml we saved in the loop before back into the
     * final validation.xml again. the final result should be one validation.xml including all
     * the validation rules for all the documents in this bundle.
     *
     * @todo we might just make this an option to the resource generator, i need to grok why this was an issue
     *
     * @param XmlManipulator $xmlManipulator Helper to safe the validation xml file.
     * @param string         $location       Location where to store the file.
     *
     * @return void
     */
    private function generateValidationXml(XmlManipulator $xmlManipulator, $location)
    {
        if (file_exists($location)) {
            $xmlManipulator
                ->renderDocument(file_get_contents($location))
                ->saveDocument($location);
        }
    }

    /**
     * Returns an XMLElement from a generated validation.xml that was generated during Resources generation.
     *
     * @param string $namespace Namespace, ie GravitonDyn\ShowcaseBundle
     *
     * @return \SimpleXMLElement The element
     *
     * @deprecated is this really used?
     */
    public function getGeneratedValidationXml($namespace)
    {
        $validationXmlPath = $this->getGeneratedValidationXmlPath($namespace);
        if (file_exists($validationXmlPath)) {
            $validationXml = new \SimpleXMLElement(file_get_contents($validationXmlPath));
            $validationXml->registerXPathNamespace('sy', 'http://symfony.com/schema/dic/constraint-mapping');
        } else {
            throw new \LogicException('Could not find ' . $validationXmlPath . ' that should be generated.');
        }

        return $validationXml;
    }
}
