<?php
// src/Acme/DemoBundle/Command/GreetCommand.php
namespace Graviton\RestBundle\Command;


use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Sensio\Bundle\GeneratorBundle\Command\GenerateBundleCommand as ParentCommand;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Sensio\Bundle\GeneratorBundle\Command\GeneratorCommand;

class CreateService extends GeneratorCommand
{

		protected function configure()
		{			
			$this->setDefinition(array(
                new InputOption('namespace', '', InputOption::VALUE_REQUIRED, 'The namespace of the bundle to create'),
            ))
            ->setDescription('Create a basic restbundle')
			->setName('restbundle:create');
			
			/*
			$this
			 ->setName('restbundle:create')
			->setDescription('Greet someone')
			->addOption('namespace', '', InputOption::VALUE_REQUIRED, 'The namespace of the bundle to create');
			
			//->addOption('structure', '', InputOption::VALUE_NONE, 'Whether to generate the whole directory structure');*/
			
		}
	
		protected function execute(InputInterface $input, OutputInterface $output)
		{		
			$format = 'xml';
			$stucture = 'yes';
			$dialog = $this->getDialogHelper();
			
			$connection = $dialog->ask($output, $dialog->getQuestion('Connection Name', 'default'), 'default');
			$entityName = $dialog->ask($output, $dialog->getQuestion('Name of the entity/document  (TestEntity) ', ''), '');
			$entityDir = $dialog->ask($output, $dialog->getQuestion('Name of the folder containing your entities/documents  (Entity/Document) ', 'Entity'), 'Entity');
			$prefix = $dialog->ask($output, $dialog->getQuestion('Prefix for this route (/test) ', '/'.strtolower($entityName)), '/'.strtolower($entityName));
			$generateModel = $dialog->ask($output, $dialog->getQuestion('Do you want to create a doctrine entity/document after completion?', 'yes'), 'yes');

			
			$command = $this->getApplication()->find('generate:bundle');
			$objGenerator = new BundleGenerator($this->getContainer()->get('filesystem'), $this->getContainer(), $connection, $entityName, $entityDir, $input->getOption('namespace'), $prefix);

			$command->setGenerator($objGenerator);

			$return = $command->run($input, $output);
			
			if ($generateModel == 'yes') {
				$this->runEntityGeneratorCommand($input, $output);
			}

			//$text = 'ggu';
			
			//$output->writeln($text);

		}
		
		protected function createGenerator()
		{
			return new BundleGenerator($this->getContainer()->get('filesystem'));
		}
		
		private function runBundleGeneratorCommand(InputInterface $input, OutputInterface $output)
		{
			$command = $this->getApplication()->find('generate:bundle');
			$objGenerator = new BundleGenerator($this->getContainer()->get('filesystem'), $this->getContainer(), $this->getContainer()->get('kernel')->getRootDir());
			
			$command->setGenerator($objGenerator);
			
			$commandReturn = $command->run($input, $output);
			
			return $commandReturn;
		}
		
		private function runEntityGeneratorCommand(InputInterface $input, OutputInterface $output)
		{
			$command = $this->getApplication()->find('doctrine:generate:entity');
			
			$input = new ArrayInput(array());
				
			$commandReturn = $command->run($input, $output);
				
			return $commandReturn;
		}
}