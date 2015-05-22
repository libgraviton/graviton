<?php
/**
 * validate mapping of names to singular
 */

namespace Graviton\GeneratorBundle\Tests\Generator\ResourceGenerator;

use \Graviton\GeneratorBundle\Generator\ResourceGenerator\FieldNameMapper;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class FieldNameMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider mapData
     *
     * @param string $plural   plural form of string
     * @param string $singular singular form of string
     *
     * @return void
     */
    public function testMap($plural, $singular)
    {
        $sut = new FieldNameMapper;

        $field = [
            'fieldName' => $plural
        ];
        $expected = [
            'fieldName' => $plural,
            'singularName' => $singular
        ];
        $this->assertEquals($expected, $sut->map($field));
    }

    /**
     * @return array
     */
    public function mapData()
    {
        return [
            ['patches', 'patch'],
            ['names', 'name'],
            ['fields', 'field'],
            ['buses', 'bus'],
        ];
    }
}
