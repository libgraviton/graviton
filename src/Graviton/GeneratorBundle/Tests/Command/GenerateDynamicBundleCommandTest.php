<?php
/**
 * functional tests for graviton:generate:dynamicbundles
 */

namespace Graviton\GeneratorBundle\Tests\Command;

use Graviton\GeneratorBundle\Command\GenerateDynamicBundleCommand;
use Sensio\Bundle\GeneratorBundle\Tests\Command\GenerateBundleCommandTest as BaseTest;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateDynamicBundleCommandTest extends BaseTest
{
    /**
     * @return void
     */
    public function testGenerateDynamicBundleExpectingException()
    {
        $loaderDouble = $this->getMockBuilder('\Graviton\GeneratorBundle\Definition\Loader\LoaderInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('load'))
            ->getMockForAbstractClass();
        $loaderDouble
            ->expects($this->once())
            ->method('load')
            ->willReturn(array());

        $processDouble = $this->getMockBuilder('\Symfony\Component\Process\Process')
            ->disableOriginalConstructor()
            ->setMethods(array('setCommandLine', 'run', 'isSuccessful', 'getErrorOutput', 'getExitCode'))
            ->getMock();

        $commando = new GenerateDynamicBundleCommand(
            $this->getContainerDouble($loaderDouble),
            $processDouble
        );

        $application = new Application();
        $application->add($commando);

        $command = $application->find('graviton:generate:dynamicbundles');

        $tester = new CommandTester($command);

        $this->setExpectedException('\LogicException');

        $tester->execute(
            array_merge(
                array('command' => $command->getName()),
                array('--json' => __DIR__ . '/Resources/Definition/testDefinition.json')
            )
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getContainerDouble($loaderDouble)
    {
        $container = $this->getMockBuilder('\Symfony\Component\DependencyInjection\ContainerInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('get'))
            ->getMockForAbstractClass();

        $container
            ->expects($this->once())
            ->method('get')
            ->with('graviton_generator.definition.loader')
            ->willReturn($loaderDouble);

        return $container;
    }
}
