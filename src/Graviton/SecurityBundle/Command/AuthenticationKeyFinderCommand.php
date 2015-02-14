<?php

namespace Graviton\SecurityBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AuthenticationKeyFinderCommand
 *
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class AuthenticationKeyFinderCommand extends Command
{
    /**
     * @var array
     */
    private $strategies = array();


    /**
     * @param string $service
     */
    public function addService($service)
    {
        $this->strategies[] = $service;
        $this->strategies = array_unique($this->strategies);
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('graviton:security:authenication:keyfinder:strategies')
            ->setDescription('Shows a list of available keyfinder strategies.')
            ->addOption(
                'list',
                'l',
                InputOption::VALUE_NONE,
                'If set, it will provide a list of available strategies.'
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Following strategies to extract an authentication key are available:</info>');
        $output->writeln(
            "<info>\t* " .
            implode(PHP_EOL . "\t* ", $this->strategies) .
            "</info>"
        );
    }
}
