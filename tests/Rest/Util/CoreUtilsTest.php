<?php
/**
 * test core utils
 */

namespace Graviton\Tests\Rest\Util;

use Graviton\CoreBundle\Util\CoreUtils;
use PHPUnit\Framework\TestCase;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class CoreUtilsTest extends TestCase
{

    /**
     * tests if string list parsing is fine
     *
     * @dataProvider parseStringFieldListDataProvider
     *
     * @param string $stringList string list
     * @param array  $expected   expected
     *
     * @return void
     */
    public function testParseStringFieldList($stringList, $expected)
    {
        $this->assertEquals(
            $expected,
            CoreUtils::parseStringFieldList($stringList)
        );
    }

    /**
     * test data
     *
     * @return array test data
     */
    public static function parseStringFieldListDataProvider(): array
    {
        return [
            [
                null,
                []
            ],
            [
                '',
                []
            ],
            [
                ' hans ',
                [
                    'hans' => [
                        'name' => 'hans',
                        'type' => 'string'
                    ]
                ]
            ],
            [
                ' hans, int: hans2 ',
                [
                    'hans' => [
                        'name' => 'hans',
                        'type' => 'string'
                    ],
                    'hans2' => [
                        'name' => 'hans2',
                        'type' => 'int'
                    ]
                ]
            ],
            [
                'test: hans , int:hans2, bool:hans3 ',
                [
                    'hans' => [
                        'name' => 'hans',
                        'type' => 'test'
                    ],
                    'hans2' => [
                        'name' => 'hans2',
                        'type' => 'int'
                    ],
                    'hans3' => [
                        'name' => 'hans3',
                        'type' => 'bool'
                    ]
                ]
            ],
        ];
    }
}
