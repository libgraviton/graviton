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

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
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

        $kernelDouble = $this->getMock('\Symfony\Component\HttpKernel\KernelInterface');
        $runnerDouble = $this->getMockBuilder('\Graviton\GeneratorBundle\CommandRunner')
            ->setConstructorArgs(array($kernelDouble, $processDouble))
            ->getMock();
        $xmlManipulatorDouble = $this->getMock('\Graviton\GeneratorBundle\Manipulator\File\XmlManipulator');

        $commando = new GenerateDynamicBundleCommand(
            $this->getContainerDouble($loaderDouble, $kernelDouble),
            $runnerDouble,
            $xmlManipulatorDouble
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
     * Provides a test double of the service container.
     *
     * @param object $loaderDouble test double for a definition loader
     * @param object $kernelDouble test double of the SF2 kernel
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getContainerDouble($loaderDouble, $kernelDouble)
    {
        $containerDouble = $this->getMockBuilder('\Symfony\Component\DependencyInjection\ContainerInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('get'))
            ->getMockForAbstractClass();
        $containerDouble
            ->expects($this->exactly(2))
            ->method('get')
            ->willReturnOnConsecutiveCalls($loaderDouble, $kernelDouble);

        return $containerDouble;
    }

    /**
     * @return void
     */
    public function testGenerateSubResourcesFieldNotAHash()
    {
        $outputDouble = $this->getMockBuilder('\Symfony\Component\Console\Output\OutputInterface')
            ->getMockForAbstractClass();

        $isHash = false;
        $jsonField = $this->getDefinitionElementDouble($isHash);
        $xmlManipulatorDouble = $this->getMock('\Graviton\GeneratorBundle\Manipulator\File\XmlManipulator');

        $this->executeGenerateSubresources(
            $outputDouble,
            $this->getJsonDefDouble(array($jsonField)),
            $xmlManipulatorDouble
        );
    }

    /**
     * @return void
     */
    public function testGenerateSubResourcesFieldABapOfPrimitives()
    {
        $outputDouble = $this->getMockBuilder('\Symfony\Component\Console\Output\OutputInterface')
            ->getMockForAbstractClass();

        $isHash = true;
        $isBagOfPrimitives = true;
        $jsonField = $this->getDefinitionElementDouble($isHash, $isBagOfPrimitives);
        $xmlManipulatorDouble = $this->getMock('\Graviton\GeneratorBundle\Manipulator\File\XmlManipulator');

        $this->executeGenerateSubresources(
            $outputDouble,
            $this->getJsonDefDouble(array($jsonField)),
            $xmlManipulatorDouble
        );
    }

    /**
     * @return void
     */
    public function testGenerateSubResourcesFieldNoFields()
    {
        $outputDouble = $this->getMockBuilder('\Symfony\Component\Console\Output\OutputInterface')
            ->getMockForAbstractClass();
        $xmlManipulatorDouble = $this->getMock('\Graviton\GeneratorBundle\Manipulator\File\XmlManipulator');

        $this->executeGenerateSubresources(
            $outputDouble,
            $this->getJsonDefDouble(),
            $xmlManipulatorDouble
        );
    }

    /**
     * @return void
     */
    public function testGenerateSubResources()
    {
        $jsonField = $this->getMockBuilder('\Graviton\GeneratorBundle\Definition\DefinitionElementInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $kernelDouble = $this->getMockBuilder('\Symfony\Component\HttpKernel\KernelInterface')
            ->getMock();

        $containerDouble = $this->getContainerDouble($jsonField, $kernelDouble);

        $processDouble = $this->getMockBuilder('\Symfony\Component\Process\Process')
            ->disableOriginalConstructor()
            ->getMock();

        $runnerDouble = $this->getMockBuilder('\Graviton\GeneratorBundle\CommandRunner')
            ->setConstructorArgs(array($kernelDouble, $processDouble))
            ->getMock();

        $outputDouble = $this->getMockBuilder('\Symfony\Component\Console\Output\OutputInterface')
            ->getMockForAbstractClass();

        $xmlManipulatorDouble = $this->getMock('\Graviton\GeneratorBundle\Manipulator\File\XmlManipulator');

        /** @var \Graviton\GeneratorBundle\Command\GenerateDynamicBundleCommand $command */
        $command = $this->getProxyBuilder('\Graviton\GeneratorBundle\Command\GenerateDynamicBundleCommand')
            ->setConstructorArgs(array($containerDouble, $runnerDouble, $xmlManipulatorDouble))
            ->setMethods(array('generateSubResources'))
            ->getProxy();

        $command->generateSubResources(
            $outputDouble,
            $this->getJsonDefDouble(array($jsonField)),
            $xmlManipulatorDouble,
            'MyTestBundle',
            '\MyNamespace\Test'
        );

    }

    /**
     * @param object $outputDouble         test double for the output stgream
     * @param object $jsonDefDouble        test double for the json configuration
     * @param object $xmlManipulatorDouble test double for the manipulator
     *
     * @throws \Exception
     * @return void
     */
    public function executeGenerateSubresources($outputDouble, $jsonDefDouble, $xmlManipulatorDouble)
    {
        /** @var \Graviton\GeneratorBundle\Command\GenerateDynamicBundleCommand $command */
        $command = $this->getProxyBuilder('\Graviton\GeneratorBundle\Command\GenerateDynamicBundleCommand')
            ->disableOriginalConstructor()
            ->setMethods(array('generateSubResources'))
            ->getProxy();

        $command->generateSubResources(
            $outputDouble,
            $jsonDefDouble,
            $xmlManipulatorDouble,
            array(),
            'MyTestBundle',
            '\MyNamespace\Test'
        );
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
     * @param array $fields set of field to be configured
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
     * @param bool $isHash            Indicates if the double imitates a hash value
     * @param bool $isBagOfPrimitives Indicates if the double imitates a set of primitive var types
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

        if (true === $isHash) {
            $jsonField
                ->expects($this->once())
                ->method('isBagOfPrimitives')
                ->willReturn($isBagOfPrimitives);

            if (false === $isBagOfPrimitives) {
                $jsonField
                    ->expects($this->once())
                    ->method('getDefFromLocal')
                    ->willReturn(
                        [
                            "id" => "myClass",
                            "target" => ["fields" => []],
                            'isSubDocument' => true
                        ]
                    );
            }
        }

        return $jsonField;
    }
}
