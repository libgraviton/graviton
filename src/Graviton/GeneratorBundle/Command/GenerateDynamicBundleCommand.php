<?php
/**
 * generate dynamic bundles
 */

namespace Graviton\GeneratorBundle\Command;

use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Graviton\GeneratorBundle\Generator\DynamicBundleBundleGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Process;

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
    private $bundleBundleNamespace;
    private $bundleBundleDir;
    private $bundleBundleClassname;
    private $bundleBundleClassfile;
    private $bundleBundleList = array();
    private $container;
    private $process;
    private $validationXmlNodes;

    /**
     * @param ContainerInterface $container Symfony dependency injection container
     * @param Process            $process   Symfony Process component
     * @param string|null        $name      The name of the command; passing null means it must be set in configure()
     */
    public function __construct(ContainerInterface $container, Process $process, $name = null)
    {
        parent::__construct($name);

        $this->container = $container;
        $this->process = $process;
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
                    to a single JSON file or a directory path containing multipl files.'
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
        $bundleNameMask = 'GravitonDyn/%sBundle';

        /**
         * GENERATE THE BUNDLEBUNDLE
         */
        $namespace = sprintf(
            $bundleNameMask,
            'Bundle'
        );
        $bundleName = str_replace(
            '/',
            '',
            $namespace
        );

        // bundlebundle stuff..
        $this->bundleBundleNamespace = $namespace;
        $this->bundleBundleDir = $input->getOption('srcDir') . $namespace;
        $this->bundleBundleClassname = $bundleName;
        $this->bundleBundleClassfile = $this->bundleBundleDir . '/'
            . $this->bundleBundleClassname . '.php';

        $filesToWorkOn = $this
            ->container
            ->get('graviton_generator.definition.loader')
            ->load($input->getOption('json'));

        // bundles in mongodb?
        // @todo this should move to loader
        foreach ($this->getDefinitionsFromMongoDb() as $mongoDef) {
            $filesToWorkOn[] = new JsonDefinition($mongoDef);
        }

        if (count($filesToWorkOn) < 1) {
            throw new \LogicException("Could not find any usable JSON files.");
        }

        /**
         * GENERATE THE BUNDLE(S)
         */
        foreach ($filesToWorkOn as $jsonDef) {
            // @todo: resulting thisIdName will not match to SF2 nameing conventions
            // $thisIdName = ucfirst(strtolower($jsonDef->getId()));

            $thisIdName = $jsonDef->getId();
            $namespace = sprintf(
                $bundleNameMask,
                $thisIdName
            );

            $jsonDef->setNamespace($namespace);

            $bundleName = str_replace('/', '', $namespace);

            $genStatus = $this->generateBundle(
                $namespace,
                $bundleName,
                $input,
                $output
            );

            if ($genStatus !== 0) {
                throw new \LogicException('Create bundle call failed, see above. Exiting.');
            }

            $this->bundleBundleList[] = $namespace;

            // re-generate our bundlebundle..
            $this->generateBundleBundleClass();

            // here we collect main class nodes from validation.xml files
            $this->validationXmlNodes = array();

            /**
             * GENERATE SUB-RESOURCES (HASHES)..
             */
            foreach ($jsonDef->getFields() as $field) {
                if ($field->isHash() && !$field->isBagOfPrimitives()) {
                    // get json for this hash and save to temp file..
                    $tempPath = tempnam(sys_get_temp_dir(), 'jsg_');
                    file_put_contents($tempPath, json_encode($field->getDefFromLocal()));

                    $arguments = array(
                        'graviton:generate:resource',
                        '--entity' => $bundleName . ':' . $field->getClassName(),
                        '--format' => 'xml',
                        '--json' => $tempPath,
                        '--fields' => $this->getFieldString(new JsonDefinition($tempPath)),
                        '--with-repository' => null,
                        '--no-controller' => 'true'
                    );

                    $genStatus = $this->executeCommand(
                        $arguments,
                        $output
                    );

                    // throw away the temp json ;-)
                    unlink($tempPath);

                    if ($genStatus !== 0) {
                        throw new \LogicException('Create subresource call failed, see above. Exiting.');
                    }

                    // look for validation.xml and save it from over-writing ;-)
                    // we basically get the xml content that was generated in order to save them later..
                    $validationXml = $this->getGeneratedValidationXmlPath($namespace);
                    if (file_exists($validationXml)) {
                        $this->validationXmlNodes[] = file_get_contents($validationXml);
                    }
                }
            }

            /**
             * GENERATE THE MAIN RESOURCE(S)
             */
            $arguments = array(
                'graviton:generate:resource',
                '--entity' => $bundleName . ':' . $thisIdName,
                '--json' => $jsonDef->getFilename(),
                '--format' => 'xml',
                '--fields' => $this->getFieldString($jsonDef),
                '--with-repository' => null
            );

            // controller?
            if (!$jsonDef->hasController() || $this->isNotWhitelistedController($jsonDef->getRouterBase())) {
                $arguments['--no-controller'] = 'true';
            }

            // don't generate if no fields..
            if (strlen($arguments['--fields']) > 0) {
                $genStatus = $this->executeCommand(
                    $arguments,
                    $output
                );
            }

            if ($genStatus !== 0) {
                throw new \LogicException('Create resource call failed, see above. Exiting.');
            }

            /**
             * what are we doing here?
             * well, when we started to generate our subclasses (hashes in our own service) as own
             * Document classes, i had the problem that the validation.xml always got overwritten by the
             * console task. sadly, validation.xml is one file for all classes in the bundle.
             * so here we merge the generated validation.xml we saved in the loop before back into the
             * final validation.xml again. the final result should be one validation.xml including all
             * the validation rules for all the documents in this bundle.
             *
             * @todo we might just make this an option to the resource generator, i need to grok why this was an issue
             */
            if (count($this->validationXmlNodes) > 0) {
                $validationXml = $this->getGeneratedValidationXmlPath($namespace);
                if (file_exists($validationXml)) {
                    $doc = new \DOMDocument();
                    $doc->formatOutput = true;
                    $doc->preserveWhiteSpace = false;
                    $doc->load($validationXml);

                    foreach ($this->validationXmlNodes as $xmlNode) {
                        $mergeDoc = new \DOMDocument();
                        $mergeDoc->formatOutput = true;
                        $mergeDoc->preserveWhiteSpace = false;
                        $mergeDoc->loadXML($xmlNode);

                        $importNode = $mergeDoc->getElementsByTagNameNS(
                            'http://symfony.com/schema/dic/constraint-mapping',
                            'class'
                        )->item(0);

                        $importNode = $doc->importNode($importNode, true);
                        $doc->documentElement->appendChild($importNode);
                    }

                    // generate new validation.xml
                    $doc->save($validationXml);
                }
            }

            $output->writeln('');
            $output->writeln(sprintf('<info>Generated "%s" from file %s</info>', $bundleName, $jsonDef->getFilename()));
            $output->writeln('');
        }
    }

    /**
     * Generates a Bundle via command line (wrapping graviton:generate:bundle)
     *
     * @param string          $namespace  Namespace
     * @param string          $bundleName Name of bundle
     * @param InputInterface  $input      Input
     * @param OutputInterface $output     Output
     *
     * @return integer|null The exit code
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

        return $this->executeCommand(
            $arguments,
            $output
        );
    }

    /**
     * Executes a app/console command
     *
     * @param array           $args   Arguments
     * @param OutputInterface $output Output
     *
     * @return integer|null Exit code
     */
    private function executeCommand(array $args, OutputInterface $output)
    {
        // get path to console from kernel..
        $consolePath = $this->container->get('kernel')->getRootDir() . '/console';

        $cmd = 'php ' . $consolePath . ' -n ';
        $name = $args[0];

        foreach ($args as $key => $val) {
            if (strlen($key) > 1) {
                $cmd .= ' ' . $key;
            }
            if (strlen($key) > 1 && !is_null($val)) {
                $cmd .= '=';
            }
            if (strlen($val) > 1) {
                $cmd .= escapeshellarg($val);
            }
        }

        $output->writeln('');
        $output->writeln(
            sprintf(
                '<info>Running %s</info>',
                $name
            )
        );

        $output->writeln(
            sprintf(
                '<comment>%s</comment>',
                $cmd
            )
        );

        $this->process->setCommandLine($cmd);
        $this->process->run(
            function ($type, $buffer) use ($output) {
                if (Process::ERR === $type) {
                    $output->writeln(
                        sprintf(
                            '<error>%s</error>',
                            $buffer
                        )
                    );
                } else {
                    $output->writeln(
                        sprintf(
                            '<comment>%s</comment>',
                            $buffer
                        )
                    );
                }
            }
        );

        if (!$this->process->isSuccessful()) {
            throw new \RuntimeException($this->process->getErrorOutput());
        }

        return $this->process->getExitCode();
    }

    /**
     * Returns an XMLElement from a generated validation.xml that was generated during Resources generation.
     *
     * @param string $namespace Namespace, ie GravitonDyn\ShowcaseBundle
     *
     * @return \SimpleXMLElement The element
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

    /**
     * Returns the path to the generated validation.xml
     *
     * @param string $namespace Namespace
     *
     * @return string path
     */
    public function getGeneratedValidationXmlPath($namespace)
    {
        return dirname(__FILE__) . '/../../../' . $namespace . '/Resources/config/validation.xml';
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
        $dbbGenerator = new DynamicBundleBundleGenerator();

        // add optional bundles if defined by parameter.
        if ($this->container->hasParameter('generator.bundlebundle.additions')) {
            $additions = json_decode(
                $this->container->getParameter('generator.bundlebundle.additions'),
                true
            );
            if (is_array($additions)) {
                $dbbGenerator->setAdditions($additions);
            }
        }

        $dbbGenerator->generate(
            $this->bundleBundleList,
            $this->bundleBundleNamespace,
            $this->bundleBundleClassname,
            $this->bundleBundleClassfile
        );
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
     * As an alternative, bundle definitions can be stored in a MongoDB collection.
     * Here we look for those and return them as files to be included in the generation process.
     *
     * @return array Bundles
     *
     * @todo this should move to loader
     */
    private function getDefinitionsFromMongoDb()
    {
        $collectionName = $this->container->getParameter('generator.dynamicbundles.mongocollection');
        $files = array();

        // nothing there..
        if (strlen($collectionName) < 1) {
            return array();
        }

        $conn = $this->container->get('doctrine_mongodb.odm.default_connection')->getMongoClient();
        $collection = $conn->selectCollection(
            $this->container->getParameter('mongodb.default.server.db'),
            $collectionName
        );

        $cursor = $collection->find($this->determineSearchCriteria());

        foreach ($cursor as $doc) {
            if (isset($doc['_id'])) {
                unset($doc['_id']);
            }

            $thisFilename = tempnam(sys_get_temp_dir(), 'mongoBundle_');
            // @todo use symfony tools to write this
            file_put_contents($thisFilename, json_encode($doc));

            $files[] = $thisFilename;
        }

        return $files;
    }

    /**
     * Determines search criteria to be used.
     *
     * @return array
     */
    private function determineSearchCriteria()
    {
        $criteria = json_decode(
            $this->container->getParameter('generator.dynamicbundles.mongocollection.criteria'),
            true
        );

        return (is_array($criteria)) ? $criteria : array();
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
        // if no whitelist is set, everything is whitelisted
        if (!$this->container->hasParameter('generator.dynamicbundles.service.whitelist')) {
            return false;
        }

        // if param is there our default is 'yes' - everything is not whitelisted by default.
        $ret = true;

        $whitelist = json_decode(
            $this->container->getParameter('generator.dynamicbundles.service.whitelist'),
            true
        );

        // whitelist it if in list..
        if (is_array($whitelist) && in_array($routerBase, $whitelist)) {
            $ret = false;
        }

        return $ret;
    }
}
