<?php
/**
 * ObjectSlicerTest class file
 */

namespace Graviton\RestBundle\Tests\Utils;

use Graviton\RestBundle\Utils\ObjectSlicer;

/**
 * ObjectSlicer test
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ObjectSlicerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testError()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Value must be an array or an object');
        (new ObjectSlicer())->slice('123', 'a');
    }

    /**
     * @param object $value    Value
     * @param string $path     Slice path
     * @param object $expected Expected result
     * @return void
     *
     * @dataProvider testSliceDataProvider
     */
    public function testSlice($value, $path, $expected)
    {
        $this->assertEquals(
            $expected,
            (new ObjectSlicer())->slice($value, $path)
        );
    }

    /**
     * @return array
     */
    public function testSliceDataProvider()
    {
        return [
            'simple' => [
                (object) ['a' => 1],
                'a',
                (object) ['a' => 1],
            ],
            'deep' => [
                (object) ['a' => (object) ['b' => 2]],
                'a.b',
                (object) ['a' => (object) ['b' => 2]],
            ],

            'slice' => [
                (object) ['a' => 1, 'b' => 2],
                'a',
                (object) ['a' => 1],
            ],
            'slice deep' => [
                (object) ['a' => (object) ['b' => 2], 'c' => 3],
                'a.b',
                (object) ['a' => (object) ['b' => 2]],
            ],

            'unexist' => [
                (object) ['a' => (object) ['b' => 2]],
                'a.b.c',
                (object) ['a' => (object) []],
            ],
            'unexist slice' => [
                (object) ['a' => (object) ['b' => 2], 'c' => 3],
                'a.b.c',
                (object) ['a' => (object) []],
            ],

            'array' => [
                (object) ['a' => [(object) ['b' => 2], (object) ['b' => 2]]],
                'a.b',
                (object) ['a' => [(object) ['b' => 2], (object) ['b' => 2]]],
            ],
            'array slice' => [
                (object) ['a' => [(object) ['b' => 2], (object) ['b' => 2]], 'c' => 3],
                'a.b',
                (object) ['a' => [(object) ['b' => 2], (object) ['b' => 2]]],
            ],

            'array unexist' => [
                (object) ['a' => [(object) ['b' => 2], (object) ['b' => 2]]],
                'a.b.c',
                (object) ['a' => [(object) [], (object) []]],
            ],
            'array unexist slice' => [
                (object) ['a' => [(object) ['b' => 2], (object) ['b' => 2]], 'c' => 3],
                'a.b.c',
                (object) ['a' => [(object) [], (object) []]],
            ],

            'deep deep' => [
                (object) ['a' => (object) ['b' => (object) ['c' => (object) ['d' => 4]]]],
                'a.b.c.d',
                (object) ['a' => (object) ['b' => (object) ['c' => (object) ['d' => 4]]]],
            ],
            'deep deep part' => [
                (object) ['a' => (object) ['b' => (object) ['c' => (object) ['d' => 4]]]],
                'a.b.c',
                (object) ['a' => (object) ['b' => (object) ['c' => (object) ['d' => 4]]]],
            ],

            'deep deep slice' => [
                (object) ['a' => (object) ['b' => (object) ['c' => (object) ['d' => 4, 'e' => 5]]]],
                'a.b.c.d',
                (object) ['a' => (object) ['b' => (object) ['c' => (object) ['d' => 4]]]],
            ],
            'deep deep part slice' => [
                (object) ['a' => (object) ['b' => (object) ['c' => (object) ['d' => 4], 'e' => 5]]],
                'a.b.c',
                (object) ['a' => (object) ['b' => (object) ['c' => (object) ['d' => 4]]]],
            ],

            'deep array' => [
                (object) ['a' => [[[(object) ['b' => 2]]]]],
                'a.b',
                (object) ['a' => [[[(object) ['b' => 2]]]]],
            ],
            'deep array slice' => [
                (object) ['a' => [[[(object) ['b' => 2, 'c' => 3]]]]],
                'a.b',
                (object) ['a' => [[[(object) ['b' => 2]]]]],
            ],

            'deep multiarray' => [
                (object) ['a' => [[[(object) ['b' => 2]]], [(object) ['b' => 2]]]],
                'a.b',
                (object) ['a' => [[[(object) ['b' => 2]]], [(object) ['b' => 2]]]],
            ],
            'deep multiarray slice' => [
                (object) ['a' => [[[(object) ['b' => 2]]], [(object) ['c' => 3]]]],
                'a.b',
                (object) ['a' => [[[(object) ['b' => 2]]], [(object) []]]],
            ],

            'convert hash to object' => [
                ['a' => 1],
                'a',
                (object) ['a' => 1],
            ],
            'convert deep hash to object' => [
                ['a' => 1, 'b' => [], 'c' => ['d' => ['e' => 5]]],
                'c.d.e',
                (object) ['c' => (object) ['d' => (object) ['e' => 5]]],
            ],
        ];
    }

    /**
     * @param object $value    Value
     * @param array  $paths    Slice paths
     * @param object $expected Expected result
     * @return void
     *
     * @dataProvider testSliceMultipleDataProvider
     */
    public function testSliceMultiple($value, array $paths, $expected)
    {
        $this->assertEquals(
            $expected,
            (new ObjectSlicer())->sliceMulti($value, $paths)
        );
    }

    /**
     * @return array
     */
    public function testSliceMultipleDataProvider()
    {
        return [
            'simple' => [
                (object) ['a' => 1],
                ['a', 'b'],
                (object) ['a' => 1],
            ],
            'deep' => [
                (object) ['a' => (object) ['b' => 2]],
                ['a.b', 'a.c'],
                (object) ['a' => (object) ['b' => 2]],
            ],

            'simple multi' => [
                (object) ['a' => 1, 'b' => 2],
                ['a', 'b'],
                (object) ['a' => 1, 'b' => 2],
            ],
            'deep multi' => [
                (object) ['a' => (object) ['b' => 2, 'c' => 3], 'd' => 4],
                ['a.b', 'a.c', 'd'],
                (object) ['a' => (object) ['b' => 2, 'c' => 3], 'd' => 4],
            ],

            'slice multi' => [
                (object) ['a' => 1, 'b' => 2, 'c' => 3],
                ['a', 'b'],
                (object) ['a' => 1, 'b' => 2],
            ],
            'slice deep multi' => [
                (object) ['a' => (object) ['b' => 2, 'c' => 3], 'd' => 4],
                ['a.b', 'd'],
                (object) ['a' => (object) ['b' => 2], 'd' => 4],
            ],

            'unexist' => [
                (object) ['a' => (object) ['b' => 2], 'd' => 4],
                ['a.b.c', 'd'],
                (object) ['a' => (object) [], 'd' => 4],
            ],
            'unexist slice' => [
                (object) ['a' => (object) ['b' => 2], 'd' => 4],
                ['a.b.c', 'e'],
                (object) ['a' => (object) []],
            ],

            'array' => [
                (object) ['a' => [(object) ['b' => 2], (object) ['c' => 3]]],
                ['a.b', 'a.c'],
                (object) ['a' => [(object) ['b' => 2], (object) ['c' => 3]]],
            ],
            'array slice' => [
                (object) ['a' => [(object) ['b' => 2], (object) ['c' => 3], (object) ['d' => 4]], 'e' => 5],
                ['a.b', 'a.c'],
                (object) ['a' => [(object) ['b' => 2], (object) ['c' => 3], (object) []]],
            ],

            'deep deep' => [
                (object) ['a' => (object) ['b' => (object) ['c' => (object) ['d' => 4, 'e' => 5]]]],
                ['a.b.c.d', 'a.b.c.e'],
                (object) ['a' => (object) ['b' => (object) ['c' => (object) ['d' => 4, 'e' => 5]]]],
            ],
            'deep deep part' => [
                (object) ['a' => (object) ['b' => (object) ['c' => (object) ['d' => 4, 'e' => 5]]]],
                ['a.b.c', 'a.b.c.e'],
                (object) ['a' => (object) ['b' => (object) ['c' => (object) ['d' => 4, 'e' => 5]]]],
            ],

            'deep array' => [
                (object) ['a' => [[[(object) ['b' => 2, 'c' => 3]]]]],
                ['a.b', 'a.c'],
                (object) ['a' => [[[(object) ['b' => 2, 'c' => 3]]]]],
            ],
            'deep array slice' => [
                (object) ['a' => [[[(object) ['b' => 2, 'c' => 3, 'd' => 4]]]]],
                ['a.b', 'a.c'],
                (object) ['a' => [[[(object) ['b' => 2, 'c' => 3]]]]],
            ],

            'deep multiarray' => [
                (object) ['a' => [[[(object) ['b' => 2, 'c' => 3]]], [(object) ['b' => 2, 'd' => 4]]]],
                ['a.b', 'a.c'],
                (object) ['a' => [[[(object) ['b' => 2, 'c' => 3]]], [(object) ['b' => 2]]]],
            ],
            'deep multiarray slice' => [
                (object) ['a' => [[[(object) ['b' => 2, 'c' => 3]]], [(object) ['b' => 2, 'd' => 4]]]],
                ['a.c', 'a.d', 'a.e'],
                (object) ['a' => [[[(object) ['c' => 3]]], [(object) ['d' => 4]]]],
            ],

            'convert hash to object' => [
                ['a' => 1],
                ['a'],
                (object) ['a' => 1],
            ],
            'convert deep hash to object' => [
                ['a' => 1, 'b' => [], 'c' => ['d' => ['e' => 5]]],
                ['a', 'b', 'c'],
                (object) ['a' => 1, 'b' => [], 'c' => ['d' => ['e' => 5]]],
            ],

            'array merging' => [
                ['a' => [
                    ['b' => 2],
                    'delete',
                    ['b' => 2, 'd' => 4, 'e' => 5],
                    'delete',
                    ['f' => 6],
                    'delete',
                    ['e' => 5, 'x' => 0],
                    'delete',
                    ['delete', [['e' => 5], 'delete', [], (object) [], [['f' => 6]]]],
                ]],
                ['a.b', 'a.c', 'a.e'],
                (object) ['a' => [
                    (object) ['b' => 2],
                    (object) ['b' => 2, 'e' => 5],
                    (object) [],
                    (object) ['e' => 5],
                    [[(object) ['e' => 5], [], (object) [], [(object) []]]],
                ]],
            ],
        ];
    }
}
