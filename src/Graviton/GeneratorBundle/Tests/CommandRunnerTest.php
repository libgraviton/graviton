<?php
/**
 * Verify the methods of this class.
 */
namespace Graviton\GeneratorBundle\Tests;

use Graviton\GeneratorBundle\CommandRunner;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class CommandRunnerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testExecuteCommand()
    {
        $outputDouble = $this->getMockBuilder('\Symfony\Component\Console\Output\OutputInterface')
            ->getMockForAbstractClass();

        $processDouble = $this->getMockBuilder('\Symfony\Component\Process\Process')
            ->disableOriginalConstructor()
            ->setMethods(array('setCommandLine', 'run', 'isSuccessful', 'getErrorOutput', 'getExitCode'))
            ->getMock();
        $processDouble
            ->expects($this->once())
            ->method('run');
        $processDouble
            ->expects($this->once())
            ->method('isSuccessful')
            ->willReturn(false);

        $kernelDouble = $this->createMock('\Symfony\Component\HttpKernel\KernelInterface');
        $kernelDouble
            ->expects($this->once())
            ->method('getRootDir')
            ->willReturn('/app');

        $runner = new CommandRunner($kernelDouble, $processDouble);

        $this->expectException('\RuntimeException');
        $runner->executeCommand(array('graviton:test:command'), $outputDouble, 'test mesage');
    }
}
