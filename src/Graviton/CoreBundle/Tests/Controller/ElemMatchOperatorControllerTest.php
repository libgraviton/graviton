<?php
/**
 * ElemMatchOperatorControllerTest class file
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;
use GravitonDyn\TestCaseElemMatchOperatorBundle\DataFixtures\MongoDB\LoadTestCaseElemMatchOperatorData;

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
    public function setUp()
    {
        if (!class_exists(LoadTestCaseElemMatchOperatorData::class)) {
            $this->markTestSkipped('TestCaseElemMatchOperator definition is not loaded');
        }

        $this->loadFixtures(
            [LoadTestCaseElemMatchOperatorData::class],
            null,
            'doctrine_mongodb'
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

        sort($expectedIds);
        sort($foundIds);
        $this->assertEquals($expectedIds, $foundIds);
    }

    /**
     * Data for elemMatch() operator test
     *
     * @return array
     */
    public function dataElemMatchOperator()
    {
        return [
            'all' => [
                '',
                ['a', 'x'],
            ],

            'by simple field' => [
                sprintf(
                    'elemMatch(%s,eq(%s,%s))',
                    $this->encodeRqlString('$array'),
                    $this->encodeRqlString('$type'),
                    $this->encodeRqlString('a')
                ),
                ['a'],
            ],
            'nothing by simple field' => [
                sprintf(
                    'elemMatch(%s,eq(%s,%s))',
                    $this->encodeRqlString('$array'),
                    $this->encodeRqlString('$type'),
                    $this->encodeRqlString('not-found')
                ),
                [],
            ],

            'by extref' => [
                sprintf(
                    'elemMatch(%s,eq(%s,%s))',
                    $this->encodeRqlString('$array'),
                    $this->encodeRqlString('$ref'),
                    $this->encodeRqlString('http://localhost/core/module/b')
                ),
                ['a'],
            ],
            'nothing by extref' => [
                sprintf(
                    'elemMatch(%s,eq(%s,%s))',
                    $this->encodeRqlString('$array'),
                    $this->encodeRqlString('$ref'),
                    $this->encodeRqlString('http://localhost/core/module/not-found')
                ),
                [],
            ],

            'by two condition' => [
                sprintf(
                    'elemMatch(%s,and(eq(%s,%s),eq(%s,%s)))',
                    $this->encodeRqlString('$array'),
                    $this->encodeRqlString('$type'),
                    $this->encodeRqlString('a'),
                    $this->encodeRqlString('$name'),
                    $this->encodeRqlString('A')
                ),
                ['a'],
            ],
            'nothing by two condition' => [
                sprintf(
                    'elemMatch(%s,and(eq(%s,%s),eq(%s,%s)))',
                    $this->encodeRqlString('$array'),
                    $this->encodeRqlString('$type'),
                    $this->encodeRqlString('a'),
                    $this->encodeRqlString('$name'),
                    $this->encodeRqlString('B')
                ),
                [],
            ],

            'by two condition with extref' => [
                sprintf(
                    'elemMatch(%s,and(eq(%s,%s),eq(%s,%s)))',
                    $this->encodeRqlString('$array'),
                    $this->encodeRqlString('$type'),
                    $this->encodeRqlString('a'),
                    $this->encodeRqlString('$ref'),
                    $this->encodeRqlString('http://localhost/core/module/a')
                ),
                ['a'],
            ],
            'nothing by two condition with extref' => [
                sprintf(
                    'elemMatch(%s,and(eq(%s,%s),eq(%s,%s)))',
                    $this->encodeRqlString('$array'),
                    $this->encodeRqlString('$type'),
                    $this->encodeRqlString('a'),
                    $this->encodeRqlString('$ref'),
                    $this->encodeRqlString('http://localhost/core/module/b')
                ),
                [],
            ],

            'by two array elements' => [
                sprintf(
                    'elemMatch(%s,or(eq(%s,%s),eq(%s,%s)))',
                    $this->encodeRqlString('$array'),
                    $this->encodeRqlString('$type'),
                    $this->encodeRqlString('a'),
                    $this->encodeRqlString('$name'),
                    $this->encodeRqlString('B')
                ),
                ['a'],
            ],
            'nothing by two array elements' => [
                sprintf(
                    'elemMatch(%s,or(eq(%s,%s),eq(%s,%s)))',
                    $this->encodeRqlString('$array'),
                    $this->encodeRqlString('$type'),
                    $this->encodeRqlString('p'),
                    $this->encodeRqlString('$name'),
                    $this->encodeRqlString('q')
                ),
                [],
            ],

            'by both documents' => [
                sprintf(
                    'elemMatch(%s,or(eq(%s,%s),eq(%s,%s)))',
                    $this->encodeRqlString('$array'),
                    $this->encodeRqlString('$type'),
                    $this->encodeRqlString('a'),
                    $this->encodeRqlString('$name'),
                    $this->encodeRqlString('X')
                ),
                ['a', 'x'],
            ],
            'nothing by both documents' => [
                sprintf(
                    'elemMatch(%s,or(eq(%s,%s),eq(%s,%s)))',
                    $this->encodeRqlString('$array'),
                    $this->encodeRqlString('$type'),
                    $this->encodeRqlString('c'),
                    $this->encodeRqlString('$name'),
                    $this->encodeRqlString('d')
                ),
                [],
            ],

            'by deep array' => [
                sprintf(
                    'elemMatch(%s,eq(%s,%s))',
                    $this->encodeRqlString('$deep.$deep..$array'),
                    $this->encodeRqlString('$type'),
                    $this->encodeRqlString('aa')
                ),
                ['a'],
            ],
            'nothing by deep array' => [
                sprintf(
                    'elemMatch(%s,eq(%s,%s))',
                    $this->encodeRqlString('$deep.$deep..$array'),
                    $this->encodeRqlString('$type'),
                    $this->encodeRqlString('pp')
                ),
                [],
            ],

            'by deep and two condition' => [
                sprintf(
                    'elemMatch(%s,and(eq(%s,%s),eq(%s,%s)))',
                    $this->encodeRqlString('$deep.$deep..$array'),
                    $this->encodeRqlString('$type'),
                    $this->encodeRqlString('aa'),
                    $this->encodeRqlString('$name'),
                    $this->encodeRqlString('AA')
                ),
                ['a'],
            ],
            'nothing by deep and two condition' => [
                sprintf(
                    'elemMatch(%s,and(eq(%s,%s),eq(%s,%s)))',
                    $this->encodeRqlString('$deep.$deep..$array'),
                    $this->encodeRqlString('$type'),
                    $this->encodeRqlString('aa'),
                    $this->encodeRqlString('$type'),
                    $this->encodeRqlString('PP')
                ),
                [],
            ],

            'by deep and two condition with extref' => [
                sprintf(
                    'elemMatch(%s,and(eq(%s,%s),eq(%s,%s)))',
                    $this->encodeRqlString('$deep.$deep..$array'),
                    $this->encodeRqlString('$type'),
                    $this->encodeRqlString('aa'),
                    $this->encodeRqlString('$ref'),
                    $this->encodeRqlString('http://localhost/core/module/aa')
                ),
                ['a'],
            ],
            'nothing deep and by two condition with extref' => [
                sprintf(
                    'elemMatch(%s,and(eq(%s,%s),eq(%s,%s)))',
                    $this->encodeRqlString('$deep.$deep..$array'),
                    $this->encodeRqlString('$type'),
                    $this->encodeRqlString('aa'),
                    $this->encodeRqlString('$ref'),
                    $this->encodeRqlString('http://localhost/core/module/bb')
                ),
                [],
            ],

            'by deep and two array elements' => [
                sprintf(
                    'elemMatch(%s,or(eq(%s,%s),eq(%s,%s)))',
                    $this->encodeRqlString('$deep.$deep..$array'),
                    $this->encodeRqlString('$type'),
                    $this->encodeRqlString('aa'),
                    $this->encodeRqlString('$name'),
                    $this->encodeRqlString('BB')
                ),
                ['a'],
            ],
            'nothing by deep and two array elements' => [
                sprintf(
                    'elemMatch(%s,or(eq(%s,%s),eq(%s,%s)))',
                    $this->encodeRqlString('$deep.$deep..$array'),
                    $this->encodeRqlString('$type'),
                    $this->encodeRqlString('pp'),
                    $this->encodeRqlString('$name'),
                    $this->encodeRqlString('qq')
                ),
                [],
            ],


            'by deep and both documents' => [
                sprintf(
                    'elemMatch(%s,or(eq(%s,%s),eq(%s,%s)))',
                    $this->encodeRqlString('$deep.$deep..$array'),
                    $this->encodeRqlString('$type'),
                    $this->encodeRqlString('aa'),
                    $this->encodeRqlString('$name'),
                    $this->encodeRqlString('XX')
                ),
                ['a', 'x'],
            ],
            'nothing by deep and both documents' => [
                sprintf(
                    'elemMatch(%s,or(eq(%s,%s),eq(%s,%s)))',
                    $this->encodeRqlString('$deep.$deep..$array'),
                    $this->encodeRqlString('$type'),
                    $this->encodeRqlString('pp'),
                    $this->encodeRqlString('$name'),
                    $this->encodeRqlString('QQ')
                ),
                [],
            ],

            'by two elemMatch' => [
                sprintf(
                    'elemMatch(%s,and(eq(%s,%s),elemMatch(%s,and(eq(%s,%s),eq(%s,%s)))))',
                    $this->encodeRqlString('$deep.$deep'),
                    $this->encodeRqlString('$name'),
                    $this->encodeRqlString('A'),
                    $this->encodeRqlString('$array'),
                    $this->encodeRqlString('$type'),
                    $this->encodeRqlString('aa'),
                    $this->encodeRqlString('$name'),
                    $this->encodeRqlString('AA')
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
    private function encodeRqlString($string)
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
