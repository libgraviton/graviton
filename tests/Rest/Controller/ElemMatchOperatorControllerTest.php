<?php
/**
 * ElemMatchOperatorControllerTest class file
 */

namespace Graviton\Tests\Rest\Controller;

use Graviton\Tests\RestTestCase;
use GravitonDyn\TestCaseElemMatchOperatorBundle\DataFixtures\MongoDB\LoadTestCaseElemMatchOperatorData;
use Symfony\Component\HttpFoundation\Response;

/**
 * elemMatch() operator test
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ElemMatchOperatorControllerTest extends RestTestCase
{
    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp() : void
    {
        if (!class_exists(LoadTestCaseElemMatchOperatorData::class)) {
            $this->markTestSkipped('TestCaseElemMatchOperator definition is not loaded');
        }

        $this->loadFixturesLocal(
            [LoadTestCaseElemMatchOperatorData::class]
        );
    }

    /**
     * Test elemMatch() operator
     *
     * @param string $rqlQuery    RQL query
     * @param array  $expectedIds Expected IDs
     * @return void
     *
     * @dataProvider dataElemMatchOperator
     */
    public function testElemMatchOperator($rqlQuery, array $expectedIds)
    {
        $client = static::createRestClient();
        $client->request('GET', '/testcase/elemmatch-operator/?'.$rqlQuery);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $foundIds = array_map(
            function ($item) {
                return $item->id;
            },
            $client->getResults()
        );

        \sort($expectedIds);
        \sort($foundIds);
        $this->assertEquals($expectedIds, $foundIds);
    }

    /**
     * Data for elemMatch() operator test
     *
     * @return array
     */
    public static function dataElemMatchOperator(): array
    {
        return [
            'all' => [
                '',
                ['a', 'x'],
            ],

            'by simple field' => [
                sprintf(
                    'elemMatch(%s,eq(%s,%s))',
                    self::encodeRqlString('$array'),
                    self::encodeRqlString('$type'),
                    self::encodeRqlString('a')
                ),
                ['a'],
            ],
            'nothing by simple field' => [
                sprintf(
                    'elemMatch(%s,eq(%s,%s))',
                    self::encodeRqlString('$array'),
                    self::encodeRqlString('$type'),
                    self::encodeRqlString('not-found')
                ),
                [],
            ],

            'by extref' => [
                sprintf(
                    'elemMatch(%s,eq(%s,%s))',
                    self::encodeRqlString('$array'),
                    self::encodeRqlString('$ref'),
                    self::encodeRqlString('http://localhost/core/module/b')
                ),
                ['a'],
            ],
            'nothing by extref' => [
                sprintf(
                    'elemMatch(%s,eq(%s,%s))',
                    self::encodeRqlString('$array'),
                    self::encodeRqlString('$ref'),
                    self::encodeRqlString('http://localhost/core/module/not-found')
                ),
                [],
            ],

            'by two condition' => [
                sprintf(
                    'elemMatch(%s,and(eq(%s,%s),eq(%s,%s)))',
                    self::encodeRqlString('$array'),
                    self::encodeRqlString('$type'),
                    self::encodeRqlString('a'),
                    self::encodeRqlString('$name'),
                    self::encodeRqlString('A')
                ),
                ['a'],
            ],
            'nothing by two condition' => [
                sprintf(
                    'elemMatch(%s,and(eq(%s,%s),eq(%s,%s)))',
                    self::encodeRqlString('$array'),
                    self::encodeRqlString('$type'),
                    self::encodeRqlString('a'),
                    self::encodeRqlString('$name'),
                    self::encodeRqlString('B')
                ),
                [],
            ],

            'by two condition with extref' => [
                sprintf(
                    'elemMatch(%s,and(eq(%s,%s),eq(%s,%s)))',
                    self::encodeRqlString('$array'),
                    self::encodeRqlString('$type'),
                    self::encodeRqlString('a'),
                    self::encodeRqlString('$ref'),
                    self::encodeRqlString('http://localhost/core/module/a')
                ),
                ['a'],
            ],
            'nothing by two condition with extref' => [
                sprintf(
                    'elemMatch(%s,and(eq(%s,%s),eq(%s,%s)))',
                    self::encodeRqlString('$array'),
                    self::encodeRqlString('$type'),
                    self::encodeRqlString('a'),
                    self::encodeRqlString('$ref'),
                    self::encodeRqlString('http://localhost/core/module/b')
                ),
                [],
            ],

            'by two array elements' => [
                sprintf(
                    'elemMatch(%s,or(eq(%s,%s),eq(%s,%s)))',
                    self::encodeRqlString('$array'),
                    self::encodeRqlString('$type'),
                    self::encodeRqlString('a'),
                    self::encodeRqlString('$name'),
                    self::encodeRqlString('B')
                ),
                ['a'],
            ],
            'nothing by two array elements' => [
                sprintf(
                    'elemMatch(%s,or(eq(%s,%s),eq(%s,%s)))',
                    self::encodeRqlString('$array'),
                    self::encodeRqlString('$type'),
                    self::encodeRqlString('p'),
                    self::encodeRqlString('$name'),
                    self::encodeRqlString('q')
                ),
                [],
            ],

            'by both documents' => [
                sprintf(
                    'elemMatch(%s,or(eq(%s,%s),eq(%s,%s)))',
                    self::encodeRqlString('$array'),
                    self::encodeRqlString('$type'),
                    self::encodeRqlString('a'),
                    self::encodeRqlString('$name'),
                    self::encodeRqlString('X')
                ),
                ['a', 'x'],
            ],
            'nothing by both documents' => [
                sprintf(
                    'elemMatch(%s,or(eq(%s,%s),eq(%s,%s)))',
                    self::encodeRqlString('$array'),
                    self::encodeRqlString('$type'),
                    self::encodeRqlString('c'),
                    self::encodeRqlString('$name'),
                    self::encodeRqlString('d')
                ),
                [],
            ],

            'by deep array' => [
                sprintf(
                    'elemMatch(%s,eq(%s,%s))',
                    self::encodeRqlString('$deep.$deep..$array'),
                    self::encodeRqlString('$type'),
                    self::encodeRqlString('aa')
                ),
                ['a'],
            ],
            'nothing by deep array' => [
                sprintf(
                    'elemMatch(%s,eq(%s,%s))',
                    self::encodeRqlString('$deep.$deep..$array'),
                    self::encodeRqlString('$type'),
                    self::encodeRqlString('pp')
                ),
                [],
            ],

            'by deep and two condition' => [
                sprintf(
                    'elemMatch(%s,and(eq(%s,%s),eq(%s,%s)))',
                    self::encodeRqlString('$deep.$deep..$array'),
                    self::encodeRqlString('$type'),
                    self::encodeRqlString('aa'),
                    self::encodeRqlString('$name'),
                    self::encodeRqlString('AA')
                ),
                ['a'],
            ],
            'nothing by deep and two condition' => [
                sprintf(
                    'elemMatch(%s,and(eq(%s,%s),eq(%s,%s)))',
                    self::encodeRqlString('$deep.$deep..$array'),
                    self::encodeRqlString('$type'),
                    self::encodeRqlString('aa'),
                    self::encodeRqlString('$type'),
                    self::encodeRqlString('PP')
                ),
                [],
            ],

            'by deep and two condition with extref' => [
                sprintf(
                    'elemMatch(%s,and(eq(%s,%s),eq(%s,%s)))',
                    self::encodeRqlString('$deep.$deep..$array'),
                    self::encodeRqlString('$type'),
                    self::encodeRqlString('aa'),
                    self::encodeRqlString('$ref'),
                    self::encodeRqlString('http://localhost/core/module/aa')
                ),
                ['a'],
            ],
            'nothing deep and by two condition with extref' => [
                sprintf(
                    'elemMatch(%s,and(eq(%s,%s),eq(%s,%s)))',
                    self::encodeRqlString('$deep.$deep..$array'),
                    self::encodeRqlString('$type'),
                    self::encodeRqlString('aa'),
                    self::encodeRqlString('$ref'),
                    self::encodeRqlString('http://localhost/core/module/bb')
                ),
                [],
            ],

            'by deep and two array elements' => [
                sprintf(
                    'elemMatch(%s,or(eq(%s,%s),eq(%s,%s)))',
                    self::encodeRqlString('$deep.$deep..$array'),
                    self::encodeRqlString('$type'),
                    self::encodeRqlString('aa'),
                    self::encodeRqlString('$name'),
                    self::encodeRqlString('BB')
                ),
                ['a'],
            ],
            'nothing by deep and two array elements' => [
                sprintf(
                    'elemMatch(%s,or(eq(%s,%s),eq(%s,%s)))',
                    self::encodeRqlString('$deep.$deep..$array'),
                    self::encodeRqlString('$type'),
                    self::encodeRqlString('pp'),
                    self::encodeRqlString('$name'),
                    self::encodeRqlString('qq')
                ),
                [],
            ],


            'by deep and both documents' => [
                sprintf(
                    'elemMatch(%s,or(eq(%s,%s),eq(%s,%s)))',
                    self::encodeRqlString('$deep.$deep..$array'),
                    self::encodeRqlString('$type'),
                    self::encodeRqlString('aa'),
                    self::encodeRqlString('$name'),
                    self::encodeRqlString('XX')
                ),
                ['a', 'x'],
            ],
            'nothing by deep and both documents' => [
                sprintf(
                    'elemMatch(%s,or(eq(%s,%s),eq(%s,%s)))',
                    self::encodeRqlString('$deep.$deep..$array'),
                    self::encodeRqlString('$type'),
                    self::encodeRqlString('pp'),
                    self::encodeRqlString('$name'),
                    self::encodeRqlString('QQ')
                ),
                [],
            ],

            'by two elemMatch' => [
                sprintf(
                    'elemMatch(%s,and(eq(%s,%s),elemMatch(%s,and(eq(%s,%s),eq(%s,%s)))))',
                    self::encodeRqlString('$deep.$deep'),
                    self::encodeRqlString('$name'),
                    self::encodeRqlString('A'),
                    self::encodeRqlString('$array'),
                    self::encodeRqlString('$type'),
                    self::encodeRqlString('aa'),
                    self::encodeRqlString('$name'),
                    self::encodeRqlString('AA')
                ),
                ['a'],
            ],
        ];
    }

    /**
     * Encode RQL string
     *
     * @param string $string String
     * @return string
     */
    private static function encodeRqlString($string)
    {
        return strtr(
            rawurlencode($string),
            [
                '-' => '%2D',
                '_' => '%5F',
                '.' => '%2E',
                '~' => '%7E',
            ]
        );
    }
}
