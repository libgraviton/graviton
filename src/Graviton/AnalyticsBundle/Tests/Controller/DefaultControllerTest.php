<?php
/**
 * Test cases for basic coverage for Analytics Bundle
 */
namespace Graviton\AnalyticsBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Graviton\TestBundle\Test\RestTestCase;

/**
 * Basic functional test for Analytics
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class DefaultControllerTest extends RestTestCase
{
    /**
     * Initial setup
     * @return void
     */
    public function setUp()
    {
        $this->loadFixtures(
            array(
                'Graviton\CoreBundle\DataFixtures\MongoDB\LoadAppData',
                'GravitonDyn\CustomerBundle\DataFixtures\MongoDB\LoadCustomerData',
            ),
            null,
            'doctrine_mongodb'
        );
    }

    /**
     * test options request
     * @return void
     */
    public function testOptions()
    {
        $client = static::createRestClient();
        $client->request('OPTIONS', '/analytics/schema/app');
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
        $this->assertEquals('GET, OPTIONS', $client->getResponse()->headers->get('Access-Control-Allow-Methods'));
        $this->assertEmpty($client->getResults());
    }

    /**
     * Testing basic functionality
     * @return void
     */
    public function testIndex()
    {
        $client = static::createClient();

        // Let's get information from the schema
        $client->request('GET', '/analytics/schema/app');
        $content = $client->getResponse()->getContent();
        $schema = json_decode($content);

        // Check schema
        $sampleSchema = json_decode(
            '{
                    "title": "Application usage",
                    "description": "Data use for application access",
                    "type": "object",
                    "properties": {
                      "id": {
                        "title": "ID",
                        "description": "Unique identifier",
                        "type": "string"
                      },
                      "count": {
                        "title": "count",
                        "description": "Sum of result",
                        "type": "integer"
                      }
                    },
                    "x-params": []
                  }'
        );
        $this->assertEquals($sampleSchema, $schema);

        // Let's get information from the count
        $client->request('GET', '/analytics/app');
        $content = $client->getResponse()->getContent();
        $data = json_decode($content);

        // Counter data result of aggregate
        $sampleData = json_decode('{"_id":"app-count","count":2}');
        $this->assertEquals($sampleData, $data);
    }

    /**
     * Testing basic functionality
     * @return void
     */
    public function testApp2Index()
    {
        $client = static::createClient();

        // Let's get information from the count
        $client->request('GET', '/analytics/app2');
        $content = $client->getResponse()->getContent();
        $data = json_decode($content);

        // Counter data result of aggregate
        $sampleData = json_decode('{"_id":"app-count-2","count":1}');
        $this->assertEquals($sampleData, $data);
    }

    /**
     * Testing basic functionality
     * @return void
     */
    public function testCustomerCreateDateFilteringIndex()
    {
        $client = static::createClient();

        // Let's get information from the count
        $client->request('GET', '/analytics/customer-created-by-date');
        $content = $client->getResponse()->getContent();
        $data = json_decode($content);

        // Counter data result of aggregate
        $sampleData = json_decode(
            '[
              {
                "_id": "100",
                "customerNumber": 1100,
                "name": "Acme Corps.",
                "created_year": 2014,
                "created_month": 7
              }
            ]'
        );
        $this->assertEquals($sampleData, $data);

        // Let's get information from the count, but cached version
        $client->request('GET', '/analytics/customer-created-by-date');
        $content = $client->getResponse()->getContent();
        $data = json_decode($content);

        // Counter data result of aggregate
        $sampleData = json_decode(
            '[
              {
                "_id": "100",
                "customerNumber": 1100,
                "name": "Acme Corps.",
                "created_year": 2014,
                "created_month": 7
              }
            ]'
        );
        $this->assertEquals($sampleData, $data);
    }

    /**
     * test to see if required params are required
     *
     * @return void
     */
    public function testMissingParamExceptions()
    {
        $client = static::createClient();

        $client->request('GET', '/analytics/customer-date-with-param');
        $this->assertEquals(500, $client->getResponse()->getStatusCode());
    }

    /**
     * test to see application of param
     *
     * @dataProvider paramHandlingWithIntDataProvider
     *
     * @param int $yearFrom   year from
     * @param int $yearTo     year to
     * @param int $numRecords number of records
     *
     * @return void
     */
    public function testParamHandlingWithInt($yearFrom, $yearTo, $numRecords)
    {
        $client = static::createRestClient();
        $client->request('GET', '/analytics/customer-date-with-param?yearFrom='.$yearFrom.'&yearTo='.$yearTo);

        $this->assertEquals($numRecords, count($client->getResults()));
    }

    /**
     * data provider
     *
     * @return array data
     */
    public function paramHandlingWithIntDataProvider()
    {
        return [
            [
                1999,
                9999,
                4
            ],
            [
                2014,
                2014,
                1
            ],
            [
                2014,
                2015,
                2
            ],
            [
                2014,
                2016,
                3
            ]
        ];
    }

    /**
     * test to see application of param with int on string field, if the correct records are returned
     *
     * @dataProvider paramHandlingWithIntOnStringFieldDataProvider
     *
     * @param string $groupId    group id
     * @param int    $numRecords number of records
     * @param array  $idList     list of ids
     *
     * @return void
     */
    public function testParamHandlingWithIntOnStringField($groupId, $numRecords, $idList)
    {
        $client = static::createRestClient();
        $client->request('GET', '/analytics/customer-with-int-param-string-field?groupId='.$groupId);

        $this->assertEquals($numRecords, count($client->getResults()));

        foreach ($client->getResults() as $result) {
            $this->assertContains($result->{'_id'}, $idList);
        }
    }

    /**
     * data provider
     *
     * @return array data
     */
    public function paramHandlingWithIntOnStringFieldDataProvider()
    {
        return [
            [
                100,
                3,
                ['100', '101', '102']
            ],
            [
                200,
                3,
                ['100', '101', '103']
            ]
        ];
    }

    /**
     * test to see if the params are mentioned in the schema
     */
    public function testParamsInSchema()
    {
        $client = static::createRestClient();
        $client->request('GET', '/analytics/schema/customer-date-with-param');

        $this->assertEquals(2, count($client->getResults()->{'x-params'}));
        $this->assertEquals(true, count($client->getResults()->{'x-params'}[0]->required));
        $this->assertEquals(true, count($client->getResults()->{'x-params'}[1]->required));
    }
}
