<?php
namespace Graviton\GeneratorBundle\Command;

use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Graviton\GeneratorBundle\Generator\DynamicBundleBundleGenerator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Here, we generate all "dynamic" Graviton bundles..
 * The workflow is as
 * follows:
 *
 * * Generate a BundleBundle, implementing the GravitonBundleInterface
 * * Generate our Bundles per JSON file
 * * Creating the necessary resources and files inside the newly created
 * bundles.
 * * All that in our own GravitonDyn namespace.
 *
 * Important: Why are we using shell_exec instead of just using the
 * internal API? Well, the main problem is, that we want to add resources (like
 * Documents) to our Bundles *directly* after generating them. Using the
 * internal API, we cannot add resources there using our tools as those Bundles
 * haven't been loaded yet through the AppKernel. Using shell_exec we can do
 * that.. This shouldn't be a dealbreaker as this task is only used on
 * deployment and/or development where a shell is accessible. It should be
 * executed in the same context as the previous generator tools, and also those
 * used the shell (backtick operator to get git name/email for example).
 *
 * @category GeneratorBundle
 * @package  Graviton
 * @author   Dario Nuevo <dario.nuevo@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class GenerateDynamicBundleCommand extends ContainerAwareCommand
{

    private $bundleBundleNamespace;
    private $bundleBundleDir;
    private $bundleBundleClassname;
    private $bundleBundleClassfile;
    private $bundleBundleList = array();

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

        // file or folder?
        $jsonPath = $input->getOption('json');

        if (is_file($jsonPath)) {
            $filesToWorkOn = array($jsonPath);
        } else {
            if (is_dir($jsonPath)) {
                // search for json files we want..
                if (substr($jsonPath, -1) != '/') {
                    $jsonPath .= '/';
                }

                $filesToWorkOn = array();
                foreach (scandir($jsonPath) as $jsonFile) {
                    if (substr($jsonFile, -5) == '.json' && substr($jsonFile, 0, 1) != '_') {
                        $filesToWorkOn[] = $jsonPath . $jsonFile;
                    }
                };
            } else {
                $output->writeln('');
                $output->writeln('<info>No path given. Searching for "resources/definition" folders..</info>');
                $output->writeln('');

                // more broad scanning..
                // normally, we just look in the local 'src' folder.. BUT
                // if we find 'vendor/graviton/graviton' in our path means, we're inside a composer
                // dependency ourselves.. in that case, search the entire vendor/ folder.. ;-)
                // that we, we can find bundles wrapped as separate dependency..
                $rootDir = $this->getContainer()->get('kernel')->getRootDir();
                if (strpos($rootDir, 'vendor/graviton/graviton')) {
                    $scanDir = dirname($this->getContainer()->get('kernel')->getRootDir()).'/../../';
                } else {
                    $scanDir = $input->getOption('srcDir').'../';
                }

                $findCmd = 'find '.escapeshellarg($scanDir).
                    ' -path \'*/resources/definition*\' -iname \'*.json\'';

                $findFiles = explode("\n", shell_exec($findCmd));

                $filesToWorkOn = array();
                foreach ($findFiles as $foundFile) {
                    if (file_exists(trim($foundFile))) {
                        $filesToWorkOn[] = trim($foundFile);
                    }
                }
            }
        }

        // bundles in mongodb?
        $filesToWorkOn = array_merge($filesToWorkOn, $this->getDefinitionsFromMongoDb());

        if (count($filesToWorkOn) < 1) {
            throw new \LogicException("Could not find any usable JSON files.");
        }

        /**
         * GENERATE THE BUNDLE(S)
         */
        foreach ($filesToWorkOn as $jsonFile) {
            $jsonDef = new JsonDefinition($jsonFile);

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
                '--json' => $jsonFile,
                '--format' => 'xml',
                '--fields' => $this->getFieldString($jsonDef),
                '--with-repository' => null
            );

            // controller?
            if (!$jsonDef->hasController()) {
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
            $output->writeln(sprintf('<info>Generated "%s" from file %s</info>', $bundleName, $jsonFile));
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
     * @return string The exit code
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
     * @return string Exit code
     */
    private function executeCommand(array $args, OutputInterface $output)
    {

        // get path to console from kernel..
        $consolePath = $this->getContainer()->get('kernel')->getRootDir().'/console';

        $cmd = 'php '.$consolePath.' -n ';

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
                '<comment>Executing "%s"</comment>',
                $cmd
            )
        );

        passthru($cmd, $exitCode);

        return $exitCode;
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
     */
    private function getDefinitionsFromMongoDb()
    {
        $collectionName = $this->getContainer()->getParameter('generator.dynamicbundles.mongocollection');

        // nothing there..
        if (strlen($collectionName) < 1) {
            return array();
        }

        $conn = $this->getContainer()->get('doctrine_mongodb.odm.default_connection')->getMongoClient();
        $collection = $conn->selectCollection('db', $collectionName);
        $files = array();

        // custom criteria defined?
        $criteria = json_decode(
            $this->getContainer()->getParameter('generator.dynamicbundles.mongocollection.criteria'),
            true
        );

        if (is_array($criteria)) {
            $cursor = $collection->find($criteria);
        } else {
            // get all
            $cursor = $collection->find(array());
        }

        foreach ($cursor as $doc) {
            if (isset($doc['_id'])) {
                unset($doc['_id']);
            }

            $thisFilename = tempnam(sys_get_temp_dir(), 'mongoBundle_');
            file_put_contents($thisFilename, json_encode($doc));

            $files[] = $thisFilename;
        }

        return $files;
    }
}
