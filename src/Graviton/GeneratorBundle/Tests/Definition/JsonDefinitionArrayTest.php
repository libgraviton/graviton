<?php
/**
 * JsonDefinitionArrayTest class file
 */

namespace Graviton\GeneratorBundle\Tests\Definition;

use Graviton\GeneratorBundle\Definition\DefinitionElementInterface;
use Graviton\GeneratorBundle\Definition\JsonDefinitionArray;
use PHPUnit\Framework\TestCase;

/**
 * JsonDefinitionArray test
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class JsonDefinitionArrayTest extends TestCase
{
    /**
     * @var DefinitionElementInterface
     */
    private $element;

    /**
     * Setup test
     *
     * @return void
     */
    protected function setUp() : void
    {
        $this->element = $this->getMockBuilder('Graviton\GeneratorBundle\Definition\DefinitionElementInterface')
            ->setMethods(
                [
                'getName',
                'getType',
                'getTypeDoctrine',
                'getTypeSerializer',
                'getDefAsArray',
                ]
            )
            ->getMock();

        parent::setUp();
    }

    /**
     * Test JsonDefinitionArray::getElement()
     *
     * @return void
     */
    public function testGetElement()
    {
        $field = new JsonDefinitionArray('name', $this->element);
        $this->assertSame($this->element, $field->getElement());
    }

    /**
     * Test JsonDefinitionArray::getName()
     *
     * @return void
     */
    public function testGetName()
    {
        $name = __METHOD__;

        $field = new JsonDefinitionArray($name, $this->element);
        $this->assertEquals($name, $field->getName());
    }

    /**
     * Test JsonDefinitionArray::getType()
     *
     * @return void
     */
    public function testGetType()
    {
        $type = __METHOD__;

        $this->element
            ->expects($this->once())
            ->method('getType')
            ->willReturn($type);

        $field = new JsonDefinitionArray('name', $this->element);
        $this->assertEquals($type.'[]', $field->getType());
    }

    /**
     * Test JsonDefinitionArray::getTypeDoctrine()
     *
     * @return void
     */
    public function testGetTypeDoctrine()
    {
        $type = __METHOD__;

        $this->element
            ->expects($this->once())
            ->method('getTypeDoctrine')
            ->willReturn($type);

        $field = new JsonDefinitionArray('name', $this->element);
        $this->assertEquals($type.'[]', $field->getTypeDoctrine());
    }

    /**
     * Test JsonDefinitionArray::getTypeSerializer()
     *
     * @return void
     */
    public function testGetTypeSerializer()
    {
        $type = __METHOD__;

        $this->element
            ->expects($this->once())
            ->method('getTypeSerializer')
            ->willReturn($type);

        $field = new JsonDefinitionArray('name', $this->element);
        $this->assertEquals('array<'.$type.'>', $field->getTypeSerializer());
    }

    /**
     * Test JsonDefinitionArray::getDefAsArray()
     *
     * @return void
     */
    public function testGetDefAsArray()
    {
        $fielName = __METHOD__.__LINE__;
        $fielType = __METHOD__.__LINE__;
        $doctrineType = __METHOD__.__LINE__;
        $serializerType = __METHOD__.__LINE__;
        $parentDef = [
            'a' => __FILE__,
            'b' => __CLASS__,
            'c' => __NAMESPACE__,
        ];

        $this->element
            ->expects($this->once())
            ->method('getDefAsArray')
            ->willReturn($parentDef);
        $this->element
            ->expects($this->once())
            ->method('getType')
            ->willReturn($fielType);
        $this->element
            ->expects($this->once())
            ->method('getTypeDoctrine')
            ->willReturn($doctrineType);
        $this->element
            ->expects($this->once())
            ->method('getTypeSerializer')
            ->willReturn($serializerType);

        $field = new JsonDefinitionArray($fielName, $this->element);
        $this->assertEquals(
            array_replace(
                $parentDef,
                [
                    'name'           => $fielName,
                    'type'           => $fielType.'[]',
                    'doctrineType'   => $doctrineType.'[]',
                    'serializerType' => 'array<'.$serializerType.'>',
                ]
            ),
            $field->getDefAsArray()
        );
    }
}
