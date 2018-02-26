<?php
/**
 * validate field type mapper
 */

namespace Graviton\GeneratorBundle\Tests\Generator\ResourceGenerator;

use \Graviton\GeneratorBundle\Generator\ResourceGenerator\FieldTypeMapper;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class FieldTypeMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider mapData
     *
     * @param array $field    field to be mapped
     * @param array $expected mapped field
     *
     * @return void
     */
    public function testMap($field, $expected)
    {
        $sut = new FieldTypeMapper;

        $this->assertEquals($expected, $sut->map($field));
    }

    /**
     * @return array
     */
    public function mapData()
    {
        return [
            'simple string' => [
                ['type' => 'string'],
                ['type' => 'string', 'serializerType' => 'string'],
            ],
            'basic array' => [
                ['type' => 'array'],
                ['type' => 'array', 'serializerType' => 'array<string>'],
            ],
            'basic class' => [
                ['type' => 'StdClass[]'],
                ['type' => 'StdClass[]', 'serializerType' => 'array<StdClass>']
            ],
            'generic object' => [
                ['type' => 'object'],
                ['type' => 'object', 'serializerType' => 'array']
            ]
        ];
    }
}
