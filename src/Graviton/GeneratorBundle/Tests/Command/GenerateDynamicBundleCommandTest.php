<?php
/**
 * functional tests for graviton:generate:dynamicbundles
 */

namespace Graviton\GeneratorBundle\Tests\Command;

use Graviton\GeneratorBundle\Command\GenerateDynamicBundleCommand;
use lapistano\ProxyObject\ProxyBuilder;
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

    /**
     * @eturn void
     */
    public function testGenerateSubResourcesFieldNotAHash()
    {
        $outputDouble = $this->getMockBuilder('\Symfony\Component\Console\Output\OutputInterface')
            ->getMockForAbstractClass();

        $isHash = true;
        $jsonField = $this->getDefinitionElementDouble($isHash);

        $this->exectueGenerateSubresources($outputDouble, $this->getJsonDefDouble(array($jsonField)));
    }

    /**
     * @eturn void
     */
    public function testGenerateSubResourcesFieldABapOfPrimitives()
    {
        $outputDouble = $this->getMockBuilder('\Symfony\Component\Console\Output\OutputInterface')
            ->getMockForAbstractClass();

        $isHash = true;
        $isBagOfPrimitives = true;
        $jsonField = $this->getDefinitionElementDouble($isHash, $isBagOfPrimitives);

        $this->exectueGenerateSubresources($outputDouble, $this->getJsonDefDouble(array($jsonField)));
    }

    /**
     * @eturn void
     */
    public function testGenerateSubResourcesFieldNoFields()
    {
        $outputDouble = $this->getMockBuilder('\Symfony\Component\Console\Output\OutputInterface')
            ->getMockForAbstractClass();

        $this->exectueGenerateSubresources($outputDouble, $this->getJsonDefDouble());
    }

    /**
     * @return void
     */
    public function testGenerateSubResources()
    {
        $elementDefinitionDouble = $this->getDefinitionElementDouble();
        $kernelDouble = $this->getMockBuilder('\Symfony\Component\HttpKernel\KernelInterface')
            ->getMock();
        $kernelDouble
            ->expects($this->once())
            ->method('getRootDir')
            ->willReturn('app');

        $containerDouble = $this->getMockBuilder('\Symfony\Component\DependencyInjection\ContainerInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('get'))
            ->getMockForAbstractClass();
        $containerDouble
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturnOnConsecutiveCalls($elementDefinitionDouble, $kernelDouble);

        $processDouble = $this->getMockBuilder('\Symfony\Component\Process\Process')
            ->disableOriginalConstructor()
            ->getMock();
        $processDouble
            ->expects($this->once())
            ->method('setCommandLine')
            ->with('app/console graviton:generate:dynamicbundles --json');
        $processDouble
            ->expects($this->once())
            ->method('run');
        $processDouble
            ->expects($this->once())
            ->method('isSuccessful')
            ->willReturn(true);
        $processDouble
            ->expects($this->once())
            ->method('getExitCode')
            ->willReturn(0);

        $outputDouble = $this->getMockBuilder('\Symfony\Component\Console\Output\OutputInterface')
            ->getMockForAbstractClass();
        $outputDouble
            ->expects($this->any())
            ->method('writeln');

        $isHash = true;
        $isBagOfPrimitives = false;
        $jsonField = $this->getDefinitionElementDouble($isHash, $isBagOfPrimitives);

        /** @var \Graviton\GeneratorBundle\Command\GenerateDynamicBundleCommand $command */
        $command = $this->getProxyBuilder('\Graviton\GeneratorBundle\Command\GenerateDynamicBundleCommand')
            ->setConstructorArgs(array($containerDouble, $processDouble))
            ->setMethods(array('generateSubResources'))
            ->getProxy();

        $command->generateSubResources($outputDouble, $this->getJsonDefDouble(array($jsonField)), 'MyTestBundle');

    }

    /**
     * @param $outputDouble
     * @param $jsonDefDouble
     */
    public function exectueGenerateSubresources($outputDouble, $jsonDefDouble)
    {
        /** @var \Graviton\GeneratorBundle\Command\GenerateDynamicBundleCommand $command */
        $command = $this->getProxyBuilder('\Graviton\GeneratorBundle\Command\GenerateDynamicBundleCommand')
            ->disableOriginalConstructor()
            ->setMethods(array('generateSubResources'))
            ->getProxy();

        $command->generateSubResources($outputDouble, $jsonDefDouble, 'MyTestBundle');
    }

    /**
     * @param string $classname Name of class to be extended.
     *
     * @return ProxyBuilder
     */
    public function getProxyBuilder($classname)
    {
        return new ProxyBuilder($classname);
    }

    /**
     * @param array $fields
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getJsonDefDouble(array $fields = [])
    {
        $jsonDefDouble = $this->getMockBuilder('\Graviton\GeneratorBundle\Definition\JsonDefinition')
            ->disableOriginalConstructor()
            ->setMethods(array('getFields'))
            ->getMock();

        $jsonDefDouble
            ->expects($this->once())
            ->method('getFields')
            ->willReturn($fields);

        return $jsonDefDouble;
    }

    /**
     * @param bool $isHash
     * @param bool $isBagOfPrimitives
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getDefinitionElementDouble($isHash = false, $isBagOfPrimitives = false)
    {
        $jsonField = $this->getMockBuilder('\Graviton\GeneratorBundle\Definition\DefinitionElementInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('isHash', 'isBagOfPrimitives', 'getClassName', 'getDefFromLocal'))
            ->getMockForAbstractClass();
        $jsonField
            ->expects($this->once())
            ->method('isHash')
            ->willReturn($isHash);

        if(true === $isHash) {
            $jsonField
                ->expects($this->once())
                ->method('isBagOfPrimitives')
                ->willReturn($isBagOfPrimitives);
        }

        return $jsonField;
    }
}
