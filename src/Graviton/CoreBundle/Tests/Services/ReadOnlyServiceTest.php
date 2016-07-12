<?php
/**
 * test a readOnly service
 */

namespace Graviton\CoreBundle\Tests\Services;

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
    public function testAllowedMethod()
    {
        $client = static::createRestClient();
        $client->request('GET', "/testcase/readonly/");

        $response = $client->getResponse();
        $result = $client->getResults();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertCount(2, $result);
    }

    /**
     * test not allowed methods of a readOnly service
     *
     * @dataProvider dataProvider
     *
     * @param string $method http method
     * @param string $url    url
     * @param object $entry  entry
     *
     * @return void
     */
    public function testNotAllowedMethod($method, $url, $entry)
    {
        $client = static::createRestClient();
        $client->request($method, $url, array(), array(), array(), $entry);
        $this->assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $client->getResponse()->getStatusCode());
        $content = $client->getResults();
        $this->assertContains('No route found for "'.$method.' '.$url .'": Method Not Allowed', $content->message);
    }

    /**
     * data provider
     *
     * @return array
     */
    public function dataProvider()
    {
        $url = "/testcase/readonly/";
        $testEntry = (object) [
            "name" => "otherTest",
        ];

        return array(
            array('POST', $url, $testEntry),
            array('PUT', $url.'101', $testEntry),
            array('PUT', $url.'111', $testEntry),
            array('DELETE', $url.'101', $testEntry),
        );
    }
}
