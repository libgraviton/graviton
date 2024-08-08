<?php
/**
 * validate mapping of names to singular
 */

namespace Graviton\Tests\Generator\Generator\ResourceGenerator;

use \Graviton\GeneratorBundle\Generator\ResourceGenerator\FieldTitleMapper;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class FieldTitleMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider mapData
     *
     * @param string $name     field name form of string
     * @param string $expected expected form of string
     *
     * @return void
     */
    public function testMap($name, $expected)
    {
        $sut = new FieldTitleMapper;

        $field = [
            'fieldName' => $name
        ];
        $expected = [
            'fieldName' => $name,
            'title' => $expected
        ];
        $this->assertEquals($expected, $sut->map($field));
    }

    /**
     * @return array
     */
    public static function mapData(): array
    {
        return [
            ['names', 'Names'],
            ['busesAreNice', 'Buses are nice'],
            ['buses.are.cool', 'Buses are cool'],
            ['bus.0.name', 'Bus array name']
        ];
    }
}
