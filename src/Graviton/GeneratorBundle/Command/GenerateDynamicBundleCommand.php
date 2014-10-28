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
        //new JsonDefinition($filename)
        //$json = new JsonDefiniton($input->getOption('json'));
        
        $jsonDef = new JsonDefinition($input->getOption('json'));
        
        // compose names
        $namespace = 'GravitonDynamic/Bundle/'.ucfirst(strtolower($jsonDef->getId())).'Bundle';
        $bundleName = str_replace('/', '', $namespace);
        
        
        // first, create the bundle
        $command = $this->getApplication()->find('graviton:generate:bundle');
        $arguments = array(
            'graviton:generate:bundle',       
            '--namespace' => $namespace,
            '--bundle-name' => $bundleName,
            '--dir' => '/tmp/',
            '--format' => 'xml',
            '--structure' => 'true'
        );

        $input = new ArrayInput($arguments);
        $input->setInteractive(false);
        
        $returnCode = $command->run($input, $output);
        if($returnCode == 0) {
            echo "ok!";
                var_dump($returnCode);
        }        
        
        die;
    
        
        foreach ($json->getFields() as $field) {
            //var_dump($field->getDescription());
        }
    
        //parent::execute($input, $output);
    
        $output->writeln('Please review Resource/config/config.xml before commiting');
    }    

}
