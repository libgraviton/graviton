<?php
namespace Graviton\GeneratorBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\GenerateBundleCommand as SymfonyGenerateBundleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Graviton\GeneratorBundle\Manipulator\BundleBundleManipulator;
use Graviton\GeneratorBundle\Generator\BundleGenerator;
use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * generator command
 *
 * @category GeneratorBundle
 * @package Graviton
 * @author Lucas Bickel <lucas.bickel@swisscom.com>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link http://swisscom.ch
 */
class GenerateDynamicBundleCommand extends ContainerAwareCommand
{

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();
        
        $this->addOption('json', '', InputOption::VALUE_OPTIONAL, 'Path to the json definition.')
            ->setName('graviton:generate:dynamicbundle')
            ->setDescription('Generates a graviton dynamic bundle from a json definition');
    }

    /**
     * {@inheritDoc}
     *
     * @param InputInterface $input
     *            input
     * @param OutputInterface $output
     *            output
     *            
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $jsonDef = new JsonDefinition($input->getOption('json'));
        
        /**
         * ** GENERATE THE BUNDLE ***
         */
        
        // compose names
        $thisIdName = ucfirst(strtolower($jsonDef->getId()));
        $namespace = 'Graviton/' . $thisIdName . 'Bundle';
        $bundleName = str_replace('/', '', $namespace);
        $baseDir = dirname(__FILE__) . '/../../../';
        
        // first, create the bundle
        $command = $this->getApplication()->find('graviton:generate:bundle');
        $arguments = array(
            'graviton:generate:bundle',
            '--namespace' => $namespace,
            '--bundle-name' => $bundleName,
            '--dir' => $baseDir,
            '--format' => 'xml',
            '--structure' => 'true'
        );
        
        $input = new ArrayInput($arguments);
        $input->setInteractive(false);
        $returnCode = $command->run($input, $output);
        
        /**
         * ** GENERATE THE RESOURCE ***
         */
        
        $command = $this->getApplication()->find('graviton:generate:resource');
        $arguments = array(
            'graviton:generate:resource',
            '--entity' => $bundleName . ':' . $thisIdName,
            '--format' => 'xml',
            '--fields' => $this->getFieldString($jsonDef),
            '--with-repository' => 'true'
        );
        $input = new ArrayInput($arguments);
        $input->setInteractive(false);
        $returnCode = $command->run($input, $output);
        
        // php app/console graviton:generate:resource --entity=GravitonFooBundle:Baz --format=xml \
        // --fields="name:string isTrue:boolean consultant:Graviton\\PersonBundle\\Document\\Consultant valid:boolean contacts:Graviton\\PersonBundle\\Document\\PersonContact[] tags:array" \
        // --with-repository --no-interaction
        
        $output->writeln('Generated the bundle and the resource.');
    }

    /**
     * Returns the field string as described in the json file
     *
     * @param JsonDefinition $jsonDef
     *            The json def
     */
    private function getFieldString(JsonDefinition $jsonDef)
    {
        $ret = array();
        
        foreach ($jsonDef->getFields() as $field) {
            // don't add 'id' field it seems..
            if ($field->getName != 'id') {
                $ret[] = $field->getName() . ':' . $field->getTypeDoctrine();
            }
        }
        
        return implode(' ', $ret);
    }
}
