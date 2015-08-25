<?php
/**
 * test a readOnly service
 */

namespace Graviton\CoreBundle\Tests\WebTestCase;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Functional test
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ReadOnlyServiceTest extends RestTestCase
{
    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp()
    {
        $this->loadFixtures(
            array(
                'GravitonDyn\TestCaseReadOnlyBundle\DataFixtures\MongoDB\LoadTestCaseReadOnlyData',
            ),
            null,
            'doctrine_mongodb'
        );
    }

    /**
     * test a readOnly service
     *
     * @return void
     */
    public function testReadOnlyService()
    {
        $url = "/testcase/readonly/";

        $client = static::createRestClient();
        $client->request('GET', $url);

        $response = $client->getResponse();
        $result = $client->getResults();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertCount(2, $result);

        $testEntry = (object) [
            "name" => "otherTest",
        ];
        $client = static::createRestClient();
        $client->post($url, $testEntry);
        $this->assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $client->getResponse()->getStatusCode());
        $this->assertEquals("Method Not Allowed", $client->getResults()->error->message);

        $client = static::createRestClient();
        $client->put($url.'100', $testEntry);
        $this->assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $client->getResponse()->getStatusCode());
        $this->assertEquals("Method Not Allowed", $client->getResults()->error->message);

        $client = static::createRestClient();
        $client->request('DELETE', $url.'100', array(), array(), array(), $testEntry);
        $this->assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $client->getResponse()->getStatusCode());
        $this->assertEquals("Method Not Allowed", $client->getResults()->error->message);
    }
}
