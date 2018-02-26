<?php
/**
 * Class AuthenticationKeyFinderCommand
 */

namespace Graviton\SecurityBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class AuthenticationKeyFinderCommand extends Command
{
    /**
     * @var array
     */
    private $strategies = array();

    /**
     * @param string $service add strategy services to show
     *
     * @return void
     */
    public function addService($service)
    {
        $this->strategies[] = $service;
        $this->strategies = array_unique($this->strategies);
    }

    /**
     * {@inheritDoc}
     *
     * @return void
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
     *
     * @param InputInterface  $input  input from cli
     * @param OutputInterface $output output to cli
     *
     * @return void
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
