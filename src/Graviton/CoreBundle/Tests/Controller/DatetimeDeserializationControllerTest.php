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
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DatetimeDeserializationControllerTest extends RestTestCase
{
    /**
     * Set up the test
     *
     * @return void
     */
    public function setUp()
    {
        if (!class_exists(TestCaseDatetimeDeserialization::class)) {
            $this->markTestSkipped(sprintf('%s definition is not loaded', TestCaseDatetimeDeserialization::class));
        }
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
                '2015-12-12T12:02:16+0000',
            ],
        ];

        $client = static::createRestClient();
        $client->post('/testcase/datetime-deserialization/', $data);
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $location = $client->getResponse()->headers->get('Location');

        $client = static::createRestClient();
        $client->request('GET', $location);
        $this->assertEquals($data, $client->getResults());
    }
}
