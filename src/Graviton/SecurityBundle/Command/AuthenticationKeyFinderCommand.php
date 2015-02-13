<?php

namespace Graviton\SecurityBundle\Command;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
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
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $strategies = array();

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();

        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('graviton:security:authenication:keyfinder:strategies')
            ->setDescription('Greet someone')
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

    /**
     * @param string $service
     */
    public function addService($service)
    {
        $this->strategies[] = $service;
        $this->strategies = array_unique($this->strategies);
    }
}
