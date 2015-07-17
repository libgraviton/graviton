<?php
/**
 * functional tests for graviton:generate:dynamicbundles
 */

namespace Graviton\GeneratorBundle\Tests\Command;

use Graviton\GeneratorBundle\Command\GenerateDynamicBundleCommand;
use Graviton\GeneratorBundle\Definition\JsonDefinition;
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
        $loaderDouble = $this->getMockBuilder('Graviton\\GeneratorBundle\\Definition\\Loader\\LoaderInterface')
            ->disableOriginalConstructor()
            ->setMethods(['load'])
            ->getMockForAbstractClass();
        $loaderDouble
            ->expects($this->once())
            ->method('load')
            ->willReturn([]);

        $serializerDouble = $this->getMockBuilder('JMS\\Serializer\\SerializerInterface')
            ->setMethods(['serialize', 'deserialize'])
            ->getMockForAbstractClass();
        $serializerDouble
            ->expects($this->never())
            ->method('serialize');

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
            $runnerDouble,
            $xmlManipulatorDouble,
            $loaderDouble,
            $serializerDouble
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
     * @return array
     */
    public function definitionElementConfigProvider()
    {
        return array(
            'is no hash and no bag of primitives' => array(false, false),
            'is hash and bag of primitives' => array(true, true),
        );
    }

    /**
     * @return void
     */
    public function testGenerateSubResourcesFieldNoFields()
    {
        $this->executeGenerateSubresources(
            $this->getJsonDefDouble([])
        );
    }

    /**
     * @return void
     */
    public function testGenerateSubResourcesWithoutHashes()
    {
        $valueField = $this->getMockBuilder('Graviton\\GeneratorBundle\\Definition\\JsonDefinitionField')
            ->disableOriginalConstructor()
            ->getMock();
        $arrayField = $this->getMockBuilder('Graviton\\GeneratorBundle\\Definition\\JsonDefinitionArray')
            ->disableOriginalConstructor()
            ->getMock();

        $this->executeGenerateSubresources(
            $this->getJsonDefDouble([$valueField, $arrayField])
        );
    }

    /**
     * @return void
     */
    public function testGenerateDeepSubResources()
    {
        $subSubHashField = $this->getMockBuilder('Graviton\\GeneratorBundle\\Definition\\JsonDefinitionField')
            ->disableOriginalConstructor()
            ->getMock();

        $subHashFieldA = $this->getMockBuilder('Graviton\\GeneratorBundle\\Definition\\JsonDefinitionField')
            ->disableOriginalConstructor()
            ->getMock();
        $subHashFieldB = $this->getMockBuilder('Graviton\\GeneratorBundle\\Definition\\JsonDefinitionHash')
            ->disableOriginalConstructor()
            ->setMethods(['getJsonDefinition'])
            ->getMock();
        $subHashFieldB
            ->expects($this->exactly(2))
            ->method('getJsonDefinition')
            ->willReturn(
                $this->getJsonDefDouble([$subSubHashField])
            );

        $subArrayhashFieldA = $this->getMockBuilder('Graviton\\GeneratorBundle\\Definition\\JsonDefinitionField')
            ->disableOriginalConstructor()
            ->getMock();
        $subArrayhashFieldB = $this->getMockBuilder('Graviton\\GeneratorBundle\\Definition\\JsonDefinitionField')
            ->disableOriginalConstructor()
            ->getMock();

        $hashField = $this->getMockBuilder('Graviton\\GeneratorBundle\\Definition\\JsonDefinitionHash')
            ->disableOriginalConstructor()
            ->setMethods(['getJsonDefinition'])
            ->getMock();
        $hashField
            ->expects($this->exactly(2))
            ->method('getJsonDefinition')
            ->willReturn(
                $this->getJsonDefDouble([$subHashFieldA, $subHashFieldB])
            );

        $arrayHashField = $this->getMockBuilder('Graviton\\GeneratorBundle\\Definition\\JsonDefinitionHash')
            ->disableOriginalConstructor()
            ->setMethods(['getJsonDefinition'])
            ->getMock();
        $arrayHashField
            ->expects($this->exactly(2))
            ->method('getJsonDefinition')
            ->willReturn(
                $this->getJsonDefDouble([$subArrayhashFieldA, $subArrayhashFieldB])
            );

        $arrayField = $this->getMockBuilder('Graviton\\GeneratorBundle\\Definition\\JsonDefinitionArray')
            ->disableOriginalConstructor()
            ->setMethods(['getElement'])
            ->getMock();
        $arrayField
            ->expects($this->once())
            ->method('getElement')
            ->willReturn(
                $arrayHashField
            );

        $command = $this->getProxyBuilder('\\Graviton\\GeneratorBundle\\Command\\GenerateDynamicBundleCommand')
            ->disableOriginalConstructor()
            ->setMethods(['getSubResources'])
            ->getProxy();
        $this->assertEquals(
            [
                $subHashFieldB->getJsonDefinition(),
                $hashField->getJsonDefinition(),
                $arrayHashField->getJsonDefinition(),
            ],
            $command->getSubResources(
                $this->getJsonDefDouble([$arrayField, $hashField])
            )
        );
    }

    /**
     * @param JsonDefinition $jsonDefDouble test double for the json configuration
     * @return void
     */
    private function executeGenerateSubresources(JsonDefinition $jsonDefDouble)
    {
        $outputDouble = $this->getMock('Symfony\\Component\\Console\\Output\\OutputInterface');
        $xmlManipulatorDouble = $this->getMock('\Graviton\GeneratorBundle\Manipulator\File\XmlManipulator');

        $command = $this->getProxyBuilder('\\Graviton\\GeneratorBundle\\Command\\GenerateDynamicBundleCommand')
            ->disableOriginalConstructor()
            ->setMethods(['generateSubResources'])
            ->getProxy();

        $command->generateSubResources(
            $outputDouble,
            $jsonDefDouble,
            $xmlManipulatorDouble,
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
     * @return JsonDefinition
     */
    public function getJsonDefDouble(array $fields)
    {
        $jsonDefDouble = $this->getMockBuilder('\Graviton\GeneratorBundle\Definition\JsonDefinition')
            ->disableOriginalConstructor()
            ->setMethods(['getFields'])
            ->getMock();

        $jsonDefDouble
            ->expects($this->once())
            ->method('getFields')
            ->willReturn($fields);

        return $jsonDefDouble;
    }
}
