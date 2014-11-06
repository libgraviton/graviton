<?php
namespace Graviton\GeneratorBundle\Command;

use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Graviton\GeneratorBundle\Generator\DynamicBundleBundleGenerator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
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
        /*
                $genStatus = $this->generateBundle(
                    $namespace,
                    $bundleName,
                    $input,
                    $output
                );

                if ($genStatus !== 0) {
                    throw new \LogicException('Create BundeBundle call failed, see above. Exiting.');
                }
        */
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
                throw new \LogicException("File or path '" . $jsonPath . "' doesn't seem to exist.");
            }
        }

        if (count($filesToWorkOn) < 1) {
            throw new \LogicException("Could not find any usable JSON files.");
        }

        /**
         * GENERATE THE BUNDLE(S)
         */
        foreach ($filesToWorkOn as $jsonFile) {
            $jsonDef = new JsonDefinition($jsonFile);

            $thisIdName = ucfirst(strtolower($jsonDef->getId()));
            $namespace = sprintf(
                $bundleNameMask,
                $thisIdName
            );
            $bundleName = str_replace(
                '/',
                '',
                $namespace
            );

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

            /**
             * GENERATE SUB-RESOURCES (HASHES)..
             */
            foreach ($jsonDef->getFields() as $field) {
                if ($field->isHash()) {

                    // get json for this hash and save to temp file..
                    $tempPath = tempnam(sys_get_temp_dir(), 'jsg_');
                    file_put_contents($tempPath, json_encode($field->getDefFromLocal()));

                    $arguments = array(
                        'graviton:generate:resource',
                        '--entity' => $bundleName . ':' . $field->getClassName(),
                        '--format' => 'xml',
                        '--json' => $tempPath,
                        '--fields' => $this->getFieldString(new JsonDefinition($tempPath)),
                        '--with-repository' => null
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

            $genStatus = $this->executeCommand(
                $arguments,
                $output
            );

            if ($genStatus !== 0) {
                throw new \LogicException('Create resource call failed, see above. Exiting.');
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
        $cmd = 'php app/console -n ';

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
}
