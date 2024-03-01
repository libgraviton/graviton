<?php
/**
 * test for all the events the RestrictionListener has
 */

namespace Graviton\RestBundle\Tests\Controller;

use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Graviton\RestBundle\DataFixtures\MongoDB\LoadRestrictionListenerTestData;
use Graviton\TestBundle\Test\RestTestCase;
use GravitonDyn\TestCaseMultiTenantBundle\Document\TestCaseMultiTenant;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RestrictionListenerTest extends RestTestCase
{

    /**
     * @var DocumentRepository
     */
    private $repository;

    /**
     * custom environment
     *
     * @var string
     */
    protected static $environment = 'test_restricted';

    /**
     * custom client options
     *
     * @var string[]
     */
    private $clientOptions = ['environment' => 'test_restricted'];

    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->loadFixturesLocal(
            [
                LoadRestrictionListenerTestData::class
            ]
        );

        $this->repository = $this->getContainer()->get(
            'gravitondyn.testcasemultitenant.repository.testcasemultitenant'
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
        $client = static::createRestClient($this->clientOptions);
        $client->request('GET', '/testcase/multitenant/?sort(+value)', [], [], $serverParameters);
        $results = $client->getResults();
        $this->assertEquals($expectedCount, count($results));

        // make sure our clientId field is not rendered!
        foreach ($results as $result) {
            $this->assertObjectNotHasProperty('clientId', $result);
        }

        // with select()
        $client = static::createRestClient($this->clientOptions);
        $client->request('GET', '/testcase/multitenant/?sort(+value)&select(id)', [], [], $serverParameters);
        $results = $client->getResults();
        $this->assertEquals($expectedCount, count($results));
    }

    /**
     * test the multi tenant handling while fetching data via analytics
     *
     * @dataProvider fetchDataProvider
     *
     * @param array $serverParameters server params
     * @param int   $expectedCount    exp count
     *
     * @return void
     */
    public function testTenantFetchDataAnalytics($serverParameters, $expectedCount)
    {
        $client = static::createRestClient($this->clientOptions);
        $client->request('GET', '/analytics/restriction-multitenant?value=1', [], [], $serverParameters);
        $results = $client->getResults();
        $this->assertEquals($expectedCount, count($results));

        // make sure our clientId field is not rendered!
        foreach ($results as $result) {
            $this->assertObjectNotHasProperty('clientId', $result);
        }

        // same but for multipipeline doing the same twice
        $client->request('GET', '/analytics/restriction-multitenant-multipipeline?value=1', [], [], $serverParameters);
        $results = $client->getResults();
        $this->assertEquals($expectedCount, count($results->first));
        $this->assertEquals($expectedCount, count($results->second));

        foreach (array_merge($results->first, $results->second) as $result) {
            $this->assertObjectNotHasProperty('clientId', $result);
        }
    }

    /**
     * data provider for data fetching tests..
     *
     * @return array data
     */
    public static function fetchDataProvider(): array
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
            ],
            'client999' => [
                ['HTTP_X-GRAVITON-CLIENT' => '999'],
                2
            ]
        ];
    }

    /**
     * make sure rql cannot override anything here..
     *
     * @return void
     */
    public function testNoRqlOverride()
    {
        $client = static::createRestClient($this->clientOptions);
        $client->request(
            'GET',
            '/testcase/multitenant/?sort(+value)&eq(clientId,integer:5)',
            [],
            [],
            ['HTTP_X-GRAVITON-CLIENT' => '10']
        );
        $results = $client->getResults();

        // should have no records as it's AND 5 AND 10
        $this->assertEmpty($results);

        $client = static::createRestClient($this->clientOptions);
        $client->request(
            'GET',
            '/testcase/multitenant/?sort(+value)&eq(clientId,integer:10)',
            [],
            [],
            ['HTTP_X-GRAVITON-CLIENT' => '10']
        );
        $results = $client->getResults();

        // should have only 2 as the null entries are skipped by the rql
        $this->assertEquals(2, count($results));
        $this->assertEquals("200", $results[0]->id);
        $this->assertEquals("201", $results[1]->id);

        $client = static::createRestClient($this->clientOptions);
        $client->request(
            'GET',
            '/testcase/multitenant/?sort(+value)&or(eq(clientId,integer:10),eq(clientId,integer:5))',
            [],
            [],
            ['HTTP_X-GRAVITON-CLIENT' => '10']
        );
        $results = $client->getResults();

        // also her only 2!
        $this->assertEquals(2, count($results));
        $this->assertEquals("200", $results[0]->id);
        $this->assertEquals("201", $results[1]->id);
    }

    /**
     * tests the handling when POSTing data
     *
     * @return void
     */
    public function testTenantPostData()
    {
        $record = new \stdClass();
        $record->name = "foo";
        $record->value = 55;

        $client = static::createRestClient($this->clientOptions);
        $client->post(
            '/testcase/multitenant/',
            $record,
            [],
            [],
            [
                'HTTP_X-GRAVITON-CLIENT' => '5',
                'HTTP_X-GRAVITON-USER' => 'testposter'
            ]
        );

        $location = $client->getResponse()->headers->get('Location');

        // we sent a location header so we don't want a body
        $this->assertNull($client->getResults());
        $this->assertStringContainsString('/testcase/multitenant/', $location);

        // check it isn't visible to other tenants..
        $this->assertsRecordNotExists(6, $location);

        // but to our clientId!
        $this->assertsRecordExists(5, $location, 55, 'testposter');

        // and to no client
        $this->assertsRecordExists(null, $location, 55);
    }

    /**
     * tests the handling when PUTing data
     *
     * @return void
     */
    public function testTenantPutData()
    {
        $record = new \stdClass();
        $record->id = "103";
        $record->name = "foo";
        $record->value = 103;

        $client = static::createRestClient($this->clientOptions);
        $client->put(
            '/testcase/multitenant/103',
            $record,
            [],
            [],
            [
                'HTTP_X-GRAVITON-CLIENT' => '5',
                'HTTP_X-GRAVITON-USER' => 'tester1'
            ]
        );

        // check it isn't visible to other tenants..
        $this->assertsRecordNotExists(6, '/testcase/multitenant/103');

        // but to our clientId!
        $this->assertsRecordExists(5, '/testcase/multitenant/103', 103, 'tester1');

        // and to no client
        $this->assertsRecordExists(null, '/testcase/multitenant/103', 103, 'tester1');
    }

    /**
     * tests the handling when PUTing data with colliding IDs
     *
     * @return void
     */
    public function testTenantPutDataIdCollision()
    {
        // insert a record under client=5
        $record = new \stdClass();
        $record->id = "103";
        $record->name = "foo";
        $record->value = 103;

        $client = static::createRestClient($this->clientOptions);
        $client->put(
            '/testcase/multitenant/103',
            $record,
            [],
            [],
            [
                'HTTP_X-GRAVITON-CLIENT' => '5',
                'HTTP_X-GRAVITON-USER' => 'testerpatcher'
            ]
        );

        // make sure it exists
        $this->assertsRecordExists(5, '/testcase/multitenant/103', 103, 'testerpatcher');

        // now we want to write it again under tenant 6
        $client = static::createRestClient($this->clientOptions);
        $record->value = 3333;
        $client->put('/testcase/multitenant/103', $record, [], [], ['HTTP_X-GRAVITON-CLIENT' => '6']);

        // make sure we got a 400 error
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        // make sure nothing changed in db!
        $this->assertsRecordExists(5, '/testcase/multitenant/103', 103, 'testerpatcher');

        // clientId 6 still should not see the record
        $this->assertsRecordNotExists(6, '/testcase/multitenant/103');

        // ok.. what happens if we try to update the record with no tenant?
        $client = static::createRestClient($this->clientOptions);
        $record->value = 3334;
        $client->put('/testcase/multitenant/103', $record);

        // make sure we got an OK -> admin could write it..
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        // tenant still should be 5 but with updated value!
        $this->assertsRecordExists(5, '/testcase/multitenant/103', 3334, 'anonymous');

        // admin can see the record!
        // should be user anonymous as the last one had no header..
        $this->assertsRecordExists(null, '/testcase/multitenant/103', 3334, 'anonymous');
    }

    /**
     * tests the handling when PATCHing data
     *
     * @return void
     */
    public function testTenantPatchData()
    {
        $patchJson = json_encode(
            [
                [
                    'op' => 'replace',
                    'path' => '/value',
                    'value' => 300
                ]
            ]
        );

        // admin wants to PATCH a tenant record..
        $client = static::createRestClient($this->clientOptions);
        $client->request(
            'PATCH',
            '/testcase/multitenant/100',
            [],
            [],
            [
                'HTTP_X-GRAVITON-USER' => 'testerpatcher'
            ],
            $patchJson
        );

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        // should be under same tenant
        $this->assertsRecordExists(5, '/testcase/multitenant/100', 300, 'testerpatcher');

        // wrong tenant to other tenant
        $client = static::createRestClient($this->clientOptions);
        $client->request('PATCH', '/testcase/multitenant/100', [], [], ['HTTP_X-GRAVITON-CLIENT' => '6'], $patchJson);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        // tenant to admin
        $client = static::createRestClient($this->clientOptions);
        $client->request('PATCH', '/testcase/multitenant/1000', [], [], ['HTTP_X-GRAVITON-CLIENT' => '6'], $patchJson);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        // owner
        $patchJson = json_encode(
            [
                [
                    'op' => 'replace',
                    'path' => '/value',
                    'value' => 400
                ]
            ]
        );

        $client = static::createRestClient($this->clientOptions);
        $client->request(
            'PATCH',
            '/testcase/multitenant/100',
            [],
            [],
            [
                'HTTP_X-GRAVITON-CLIENT' => '5',
                'HTTP_X-GRAVITON-USER' => 'testerpatcher2'
            ],
            $patchJson
        );

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertsRecordExists(5, '/testcase/multitenant/100', 400, 'testerpatcher2');
    }

    /**
     * tests the handling when DELETing data
     *
     * @return void
     */
    public function testTenantDeleteData()
    {
        // admin wants to DELETE a tenant record -> he can do that!
        $client = static::createRestClient($this->clientOptions);
        $client->request('DELETE', '/testcase/multitenant/100');

        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
        $this->assertsRecordNotExists(5, '/testcase/multitenant/100');

        // load data again
        $this->setUp();

        // tenant wants to delete other tenant record -> 404 as he doesn't see it..
        $client = static::createRestClient($this->clientOptions);
        $client->request('DELETE', '/testcase/multitenant/100', [], [], ['HTTP_X-GRAVITON-CLIENT' => '6']);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        // tenant wants to delete admin record
        $client = static::createRestClient($this->clientOptions);
        $client->request('DELETE', '/testcase/multitenant/1000', [], [], ['HTTP_X-GRAVITON-CLIENT' => '6']);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        // now the owner wants to delete it
        $client = static::createRestClient($this->clientOptions);
        $client->request('DELETE', '/testcase/multitenant/100', [], [], ['HTTP_X-GRAVITON-CLIENT' => '5']);

        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
        $this->assertsRecordNotExists(5, '/testcase/multitenant/100');
    }

    /**
     * assert that a record exists
     *
     * @param mixed  $tenant         tenant
     * @param string $url            url
     * @param string $value          value
     * @param string $lastModifiedBy modified by
     *
     * @throws \Doctrine\ODM\MongoDB\LockException
     * @throws \Doctrine\ODM\MongoDB\Mapping\MappingException
     *
     * @return void
     */
    private function assertsRecordExists($tenant, $url, $value, $lastModifiedBy = null)
    {
        $server = [];
        if (!is_null($tenant)) {
            $server['HTTP_X-GRAVITON-CLIENT'] = (string) $tenant;
        }

        $client = static::createRestClient($this->clientOptions);
        $client->request('GET', $url, [], [], $server);
        $this->assertEquals($value, $client->getResults()->value);

        if (!is_null($tenant)) {
            $this->repository->getDocumentManager()->clear();
            $entity = $this->repository->find(basename($url));
            $this->assertInstanceOf(TestCaseMultiTenant::class, $entity);
            $this->assertEquals($tenant, $entity->getClientId());
        }

        if (!is_null($lastModifiedBy)) {
            $this->assertEquals($lastModifiedBy, $client->getResults()->lastModifiedBy);
            $this->assertNotNull($client->getResults()->lastModifiedBy);

            $this->repository->getDocumentManager()->clear();
            $entity = $this->repository->find(basename($url));
            $this->assertInstanceOf(TestCaseMultiTenant::class, $entity);
            $this->assertEquals($lastModifiedBy, $entity->getLastModifiedBy());
            $this->assertInstanceOf(\DateTime::class, $entity->getLastModifiedAt());
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
        $client = static::createRestClient($this->clientOptions);

        $server = [];
        if (!is_null($tenant)) {
            $server['HTTP_X-GRAVITON-CLIENT'] = (string) $tenant;
        }

        $client->request('GET', $url, [], [], $server);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }
}
