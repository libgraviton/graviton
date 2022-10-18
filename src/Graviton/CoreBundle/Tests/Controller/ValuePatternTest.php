<?php
/**
 * ValuePatternTest class file
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Graviton\TestBundle\Test\RestTestCase;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ValuePatternTest extends RestTestCase
{

    /**
     * test regexes
     *
     * @param string $value                value
     * @param int    $expectedResponseCode code
     *
     * @dataProvider dataProvider
     *
     * @return void
     */
    public function testRegexPatternCheck($value, $expectedResponseCode)
    {
        $data = [
            'id' => 'test',
            'theValue' => $value
        ];

        $client = static::createRestClient();
        $client->put('/testcase/value-pattern/test', $data);
        $this->assertEquals($expectedResponseCode, $client->getResponse()->getStatusCode());
    }

    /**
     * data provider
     *
     * @return array[] data
     */
    public function dataProvider()
    {
        return [
            [
                'dude',
                Response::HTTP_BAD_REQUEST
            ],
            [
                'test',
                Response::HTTP_BAD_REQUEST
            ],
            [
                '0hans',
                Response::HTTP_NO_CONTENT
            ],
            [
                '9-AND-MORE-hans',
                Response::HTTP_NO_CONTENT
            ]
        ];
    }
}
