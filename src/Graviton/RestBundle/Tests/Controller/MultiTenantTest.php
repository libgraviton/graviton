<?php
/**
 * test class for the "group" serialization feature
 */

namespace Graviton\RestBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use GravitonDyn\TestCaseMultiTenantBundle\DataFixtures\MongoDB\LoadTestCaseMultiTenantData;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class MultiTenantTest extends RestTestCase
{

    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp()
    {
        $this->loadFixturesLocal(
            [
                LoadTestCaseMultiTenantData::class
            ]
        );
    }

    /**
     * test the multi tenant handling while fetching data
     *
     * @dataProvider fetchDataProvider
     *
     * @param array $serverParameters server params
     * @param int   $expectedCount    exp count
     *
     * @return void
     */
    public function testTenantFetchData($serverParameters, $expectedCount)
    {
        $client = static::createRestClient();
        $client->request('GET', '/testcase/multitenant/?sort(+value)', [], [], $serverParameters);
        $results = $client->getResults();
        $this->assertEquals($expectedCount, count($results));

        // make sure our clientId field is not rendered!
        foreach ($results as $result) {
            $this->assertObjectNotHasAttribute('clientId', $result);
        }
    }

    /**
     * data provider for data fetching tests..
     *
     * @return array data
     */
    public function fetchDataProvider()
    {
        return [
            'all' => [
                [],
                6
            ],
            'client5' => [
                ['HTTP_X-GRAVITON-CLIENT' => '5'],
                4
            ],
            'client10' => [
                ['HTTP_X-GRAVITON-CLIENT' => '10'],
                4
            ]
        ];
    }
}
