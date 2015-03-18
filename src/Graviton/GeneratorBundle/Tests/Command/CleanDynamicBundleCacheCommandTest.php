<?php
/** CleanDynamicBundleCacheCommandTest **/

namespace Graviton\GeneratorBundle\Tests\Command;

use Graviton\GeneratorBundle\Command\CleanDynamicBundleCacheCommand;
use Graviton\TestBundle\Test\GravitonTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * CleanDynamicBundleCacheCommandTest
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class CleanDynamicBundleCacheCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the execution of the command
     *
     * @return void
     */
    public function testExecute()
    {
        $kernel = GravitonTestCase::createKernel();
        $application = new Application($kernel);

        $application->add(new CleanDynamicBundleCacheCommand());

        $command = $application->find('graviton:clean:dynamicbundles');
        $command->setKernel($kernel);
        $command->setFilesystem($this->getFsMock());

        $commandTester = new CommandTester($command);
        $commandTester->execute(array());
    }

    /**
     * Gets the fs mock
     *
     * @return mock
     */
    private function getFsMock()
    {
        $fs = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')
                   ->getMock();

        $fs->method('exists')
           ->willReturn(true);

        $fs->expects($this->once())
           ->method('exists')
           ->with($this->stringContains('GravitonDyn'));

        $fs->expects($this->once())
           ->method('remove')
           ->with($this->stringContains('GravitonDyn'));

        return $fs;
    }
}
