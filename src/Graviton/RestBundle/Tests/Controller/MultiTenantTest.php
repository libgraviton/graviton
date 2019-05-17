<?php
/**
 * test class for the "group" serialization feature
 */

namespace Graviton\RestBundle\Tests\Controller;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Graviton\TestBundle\Test\RestTestCase;
use GravitonDyn\TestCaseMultiTenantBundle\DataFixtures\MongoDB\LoadTestCaseMultiTenantData;
use GravitonDyn\TestCaseMultiTenantBundle\Document\TestCaseMultiTenant;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class MultiTenantTest extends RestTestCase
{

    /**
     * @var DocumentRepository
     */
    private $repository;

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

    /**
     * tests the handling when POSTing data
     *
     * @return void
     */
    public function testTenantPostData()
    {
        $this->repository = $this->getContainer()->get(
            'gravitondyn.testcasemultitenant.repository.testcasemultitenant'
        );

        $record = new \stdClass();
        $record->name = "foo";
        $record->value = 55;

        $client = static::createRestClient();
        $client->post('/testcase/multitenant/', $record, [], [], ['HTTP_X-GRAVITON-CLIENT' => '5']);

        $location = $client->getResponse()->headers->get('Location');

        // we sent a location header so we don't want a body
        $this->assertNull($client->getResults());
        $this->assertContains('/testcase/multitenant/', $location);

        // check it isn't visible to other tenants..
        $this->assertsRecordNotExists(6, $location);

        // but to our clientId!
        $this->assertsRecordExists(5, $location, 55);

        // and to no client
        $this->assertsRecordExists(null, $location, 55);
    }

    /**
     * tests the handling when an admin (= no tenant) tries to update a record with an existing clientId
     *
     * @return void
     */
    public function testTenantPutOverwriteClientIdFromAdmin()
    {
    }

    /**
     * tests the handling when PUTing data
     *
     * @return void
     */
    public function testTenantPutData()
    {
    }

    /**
     * tests the handling when DELETing data
     *
     * @return void
     */
    public function testTenantDeleteData()
    {
    }

    /**
     * assert that a record exists
     *
     * @param mixed  $tenant tenant
     * @param string $url    url
     * @param string $value  value
     *
     * @throws \Doctrine\ODM\MongoDB\LockException
     * @throws \Doctrine\ODM\MongoDB\Mapping\MappingException
     *
     * @return void
     */
    private function assertsRecordExists($tenant, $url, $value)
    {
        $server = [];
        if (!is_null($tenant)) {
            $server['HTTP_X-GRAVITON-CLIENT'] = (string) $tenant;
        }

        $client = static::createRestClient();
        $client->request('GET', $url, [], [], $server);
        $this->assertEquals($value, $client->getResults()->value);

        if (!is_null($tenant)) {
            $entity = $this->repository->find(basename($url));
            $this->assertInstanceOf(TestCaseMultiTenant::class, $entity);
            $this->assertEquals($tenant, $entity->getClientId());
        }
    }

    /**
     * asserts that a record does not exist
     *
     * @param mixed  $tenant tenant
     * @param string $url    url
     *
     * @return void
     */
    private function assertsRecordNotExists($tenant, $url)
    {
        $client = static::createRestClient();

        $server = [];
        if (!is_null($tenant)) {
            $server['HTTP_X-GRAVITON-CLIENT'] = (string) $tenant;
        }

        $client->request('GET', $url, [], [], $server);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }
}
