<?php

namespace Graviton\SecurityBundle\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class AuthenticationKeyFinderCommandTest
 *
 * @category GravitonSecurityBundle
 * @package  Graviton
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class AuthenticationKeyFinderCommandTest extends \PHPUnit_Framework_TestCase
{

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

        $this->assertContains('some Testservice', $commandTester->getDisplay());
    }

    public function testExecuteWithMultipleStrategies()
    {
        $application = $this->getApplication();
        $command = $application->find('graviton:security:authenication:keyfinder:strategies');

        $command->addService('some Testservice');
        $command->addService('some other Testservice');
        $command->addService('some Testservice');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array());

        $this->assertContains('some Testservice', $commandTester->getDisplay());
        $this->assertContains('some other Testservice', $commandTester->getDisplay());

        $this->assertAttributeEquals(array('some Testservice', 'some other Testservice'), 'strategies', $command);
        $this->assertAttributeCount(2, 'strategies', $command);
    }

    /**
     * @return Application
     */
    private function getApplication()
    {
        $kernel = $this->getMockBuilder('\Symfony\Component\HttpKernel\KernelInterface')
            ->getMockForAbstractClass();

        // mock the Kernel or create one depending on your needs
        $application = new Application($kernel);
        $application->add(new AuthenticationKeyFinderCommand());
        return $application;
    }
}
