<?php
/**
 * Runs a command using the process component.
 */

namespace Graviton\GeneratorBundle;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class CommandRunner
{
    /** @var Process */
    private $process;
    /** @var KernelInterface */
    private $kernel;

    /**
     * @param KernelInterface $kernel  Application kernel
     * @param Process         $process Process component
     */
    public function __construct(KernelInterface $kernel, Process $process)
    {
        $this->process = $process;
        $this->kernel = $kernel;
    }


    /**
     * Executes a app/console command
     *
     * @param array           $args    Arguments
     * @param OutputInterface $output  Output
     * @param string          $message Message to be shown on error.
     *
     * @return integer|null Exit code
     */
    public function executeCommand(array $args, OutputInterface $output, $message)
    {
        $name = $args[0];
        $cmd = $this->getCmd($args);

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
            function ($type, $buffer) use ($output, $cmd) {
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
            throw new \RuntimeException($message . '<error>' . $this->process->getErrorOutput() . '</error>');
        }
    }

    /**
     * get subcommand
     *
     * @param array $args args
     *
     * @return string
     */
    private function getCmd(array $args)
    {
        // get path to console from kernel..
        $consolePath = $this->kernel->getRootDir() . '/console';

        // this code was copied from Symfony\Component\Process\PhpProcess and deserves a cleanup
        $executableFinder = new PhpExecutableFinder();
        if (false === $php = $executableFinder->find()) {
            $php = null;
        }
        if ('\\' !== DIRECTORY_SEPARATOR && null !== $php) {
            // exec is mandatory to deal with sending a signal to the process
            // see https://github.com/symfony/symfony/issues/5030 about prepending
            // command with exec
            $php = 'exec '.$php;
        }

        $cmd = $php.' '.$consolePath.' -n ';

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

        return $cmd;
    }
}
