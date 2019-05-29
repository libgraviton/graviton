<?php
/**
 * Class AuthenticationKeyFinderCommandTest
 */

namespace Graviton\SecurityBundle\Command;

use Graviton\TestBundle\Test\GravitonTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class AuthenticationKeyFinderCommandTest extends GravitonTestCase
{

    /**
     * test if execute works as intended
     *
     * @return void
     */
    public function testExecute()
    {
        $application = $this->getApplication();
        $command = $application->find('graviton:security:authenication:keyfinder:strategies');

        $command->addService('some Testservice');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                '--list'  => null,
            )
        );

        $this->assertStringContainsString('some Testservice', $commandTester->getDisplay());
    }

    /**
     * check if multiple strategies at once work
     *
     * @return void
     */
    public function testExecuteWithMultipleStrategies()
    {
        $application = $this->getApplication();
        $command = $application->find('graviton:security:authenication:keyfinder:strategies');

        $command->addService('some Testservice');
        $command->addService('some other Testservice');
        $command->addService('some Testservice');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array());

        $this->assertStringContainsString('some Testservice', $commandTester->getDisplay());
        $this->assertStringContainsString('some other Testservice', $commandTester->getDisplay());

        $strategies = $this->getPrivateClassProperty($command, 'strategies')->getValue($command);

        $this->assertEquals(['some Testservice', 'some other Testservice'], $strategies);
    }

    /**
     * @return Application
     */
    private function getApplication()
    {
        $kernel = $this->getMockBuilder('\Symfony\Component\HttpKernel\KernelInterface')
            ->getMockForAbstractClass();

        $container = $this->getMockBuilder('\Symfony\Component\DependencyInjection\ContainerInterface')
            ->getMockForAbstractClass();

        $kernel->method('getBundles')->willReturn([]);
        $kernel->method('getContainer')->willReturn($container);

        // mock the Kernel or create one depending on your needs
        $application = new Application($kernel);
        $application->add(new AuthenticationKeyFinderCommand());
        return $application;
    }
}
