<?php
/**
 * DatetimeDeserializationControllerTest class file
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;
use GravitonDyn\TestCaseDatetimeDeserializationBundle\Document\TestCaseDatetimeDeserialization;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class DatetimeDeserializationControllerTest extends RestTestCase
{
    /**
     * Set up the test
     *
     * @return void
     */
    public function setUp() : void
    {
        if (!class_exists(TestCaseDatetimeDeserialization::class)) {
            $this->markTestSkipped(sprintf('%s definition is not loaded', TestCaseDatetimeDeserialization::class));
        }
        ini_set('date.timezone', 'UTC');
    }

    /**
     * Test datetime deserialization and serialization
     *
     * @return void
     */
    public function testDateDeserialization()
    {
        $data = (object) [
            'datetime'  => '2015-12-10T12:02:16+0000',
            'datetimes' => [
                '2015-12-10T10:02:16+0000',
                '2015-12-11T11:02:16+0000',
                '2015-12-12T12:02:16+0000'
            ],
        ];

        $client = static::createRestClient();
        $client->post('/testcase/datetime-deserialization/', $data);
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $location = $client->getResponse()->headers->get('Location');

        $client = static::createRestClient();
        $client->request('GET', $location);

        $result = $client->getResults();
        unset($result->id);

        $this->assertEquals($data, $result);
    }

    /**
     * Test datetime deserialization and serialization with microtime and different timezone
     *
     * @return void
     */
    public function testDateDeserializationWithMicrotime()
    {
        $data = (object) [
            'datetime'  => '2018-06-26T15:50:10.806+09:00',
            'datetimes' => [
                '2018-06-26T15:50:10.806+07:00'
            ],
        ];

        $expected = (object) [
            'datetime'  => '2018-06-26T06:50:10+0000',
            'datetimes' => [
                '2018-06-26T08:50:10+0000'
            ],
        ];

        $client = static::createRestClient();
        $client->post('/testcase/datetime-deserialization/', $data);
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $location = $client->getResponse()->headers->get('Location');

        $client = static::createRestClient();
        $client->request('GET', $location);

        $result = $client->getResults();
        unset($result->id);

        $this->assertEquals($expected, $result);
    }
}
