<?php
/**
 * validate overriding of mapping from json-defs
 */

namespace Graviton\GeneratorBundle\Tests\Generator\ResourceGenerator;

use \Graviton\GeneratorBundle\Generator\ResourceGenerator\FieldJsonMapper;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class FieldJsonMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider mapData
     *
     * @param array $field    field to map
     * @param array $def      field def ala JsonDef
     * @param array $expected expected result
     *
     * @return void
     */
    public function testMap($field, $def, $expected)
    {
        $sut = new FieldJsonMapper;

        $jsonDouble = $this->getMockBuilder('\Graviton\GeneratorBundle\Definition\JsonDefinition')
            ->disableOriginalConstructor()
            ->getMock();
        $fieldDouble = $this->getMockBuilder('\Graviton\GeneratorBundle\Definition\JsonDefinitionField')
            ->disableOriginalConstructor()
            ->getMock();

        $jsonDouble->expects($this->any())
            ->method('getField')
            ->with($field['fieldName'])
            ->willReturn($fieldDouble);

        $fieldDouble->expects($this->once())
            ->method('getDefAsArray')
            ->willReturn($def);

        $this->assertEquals($expected, $sut->map($field, $jsonDouble));
    }

    /**
     * @return array
     */
    public function mapData()
    {
        return [
            'empty jsondef' => [
                ['fieldName' => 'test'],
                [],
                ['fieldName' => 'test'],
            ],
            'string field' => [
                ['fieldName' => 'foo'],
                ['doctrineType' => 'string'],
                ['fieldName' => 'foo', 'doctrineType' => 'string', 'type' => 'string'],
            ],
        ];
    }
}
