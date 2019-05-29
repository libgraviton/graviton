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
    public function setUp() : void
    {
        $this->loadFixturesLocal(
            array(
                'Graviton\CoreBundle\DataFixtures\MongoDB\LoadAppData',
                'GravitonDyn\CustomerBundle\DataFixtures\MongoDB\LoadCustomerData',
            )
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

        $url = '/analytics/customer-with-int-param-string-field';
        if (!is_null($groupId)) {
            $url .= '?groupId='.$groupId;
        }

        $client->request('GET', $url);

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
                null, // testing default value of 100 as defined in params!
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
     * see if array properties are properly handled
     *
     * @dataProvider paramHandlingArrayWithIntOnStringFieldDataProvider
     *
     * @param string $groupId group id
     * @param array  $idList  list of ids
     *
     * @return void
     */
    public function testParamArrayHandlingWithIntOnStringField($groupId, $idList)
    {
        $client = static::createRestClient();
        $client->request(
            'GET',
            '/analytics/customer-with-int-param-array-field?groupId='.implode(',', $groupId)
        );

        $this->assertEquals(count($idList), count($client->getResults()));

        foreach ($client->getResults() as $result) {
            $this->assertContains($result->{'_id'}, $idList);
        }
    }

    /**
     * data provider
     *
     * @return array data
     */
    public function paramHandlingArrayWithIntOnStringFieldDataProvider()
    {
        return [
            [
                [100],
                ['100', '101', '102']
            ],
            [
                [200],
                ['100', '101', '103']
            ],
            [
                [100,200],
                ['100', '101', '102', '103']
            ]
        ];
    }

    /**
     * test to see if the params are mentioned in the schema
     *
     * @return void
     */
    public function testParamsInSchema()
    {
        $client = static::createRestClient();
        $client->request('GET', '/analytics/schema/customer-date-with-param');
        $results = $client->getResults();

        $this->assertEquals(2, count($results->{'x-params'}));
        $this->assertEquals(true, $results->{'x-params'}[0]->required);
        $this->assertEquals(true, $results->{'x-params'}[1]->required);
    }

    /**
     * tests conversion of MongoDates in output as well as replacement of Date instances (and maybe others)
     * in the pipeline script
     *
     * @return void
     */
    public function testDateHandlingInOutput()
    {
        $client = static::createRestClient();
        $client->request(
            'GET',
            '/analytics/customer-datehandling'
        );

        $expectedResult = [
            [
                '_id' => '100',
                'createDate' => '2014-07-15T10:23:31+0000',
                'age' => date('Y') - 2014,
                'sub' => [
                    'createDate' => '2014-07-15T10:23:31+0000'
                ]
            ],
            [
                '_id' => '101',
                'createDate' => '2015-07-15T10:23:31+0000',
                'age' => date('Y') - 2015,
                'sub' => [
                    'createDate' => '2015-07-15T10:23:31+0000'
                ]
            ],
            [
                '_id' => '102',
                'createDate' => '2016-07-15T10:23:31+0000',
                'age' => date('Y') - 2016,
                'sub' => [
                    'createDate' => '2016-07-15T10:23:31+0000'
                ]
            ],
            [
                '_id' => '103',
                'createDate' => '2017-07-15T10:23:31+0000',
                'age' => date('Y') - 2017,
                'sub' => [
                    'createDate' => '2017-07-15T10:23:31+0000'
                ]
            ],
        ];

        $this->assertEquals(
            json_decode(json_encode($expectedResult)), // make objects
            $client->getResults()
        );

        // test to see if optional date param (gt) can be used..
        $client = static::createRestClient();
        $client->request(
            'GET',
            '/analytics/customer-datehandling?dateFrom='.urlencode('2017-01-01T00:00:00+0000')
        );

        $results = $client->getResults();
        $this->assertEquals(1, count($results));
        $this->assertEquals('103', $results[0]->{'_id'});
    }

    /**
     * test handling of the multipipeline spec
     *
     * @return void
     */
    public function testMultiPipelineHandling()
    {
        $client = static::createRestClient();
        $client->request(
            'GET',
            '/analytics/multipipeline'
        );

        $results = $client->getResults();
        $this->assertEquals(6, count($results));

        // control sorting as this has to be done by our processor
        $this->assertEquals(6, $results[0]->sorter);
        $this->assertEquals(8, $results[1]->sorter);
        $this->assertEquals(11, $results[2]->sorter);
        $this->assertEquals(12, $results[3]->sorter);
        $this->assertEquals(14, $results[4]->sorter);
        $this->assertEquals(14, $results[5]->sorter);

        // the same with the optional search param
        $client = static::createRestClient();
        $client->request(
            'GET',
            '/analytics/multipipeline?search=admin'
        );

        $results = $client->getResults();
        $this->assertEquals(1, count($results));
        $this->assertEquals('admin', $results[0]->{'_id'});
    }

    /**
     * see if dbrefs as arrays get resolved in the analytics..
     *
     * @return void
     */
    public function testDbRefSolving()
    {
        $this->loadFixturesLocal(
            array(
                'GravitonDyn\ShowCaseBundle\DataFixtures\MongoDB\LoadShowCaseData'
            )
        );

        $client = static::createRestClient();
        $client->request(
            'GET',
            '/analytics/customer-showcase-refasembed'
        );

        $results = $client->getResults();

        // make sure it has been resolved
        $this->assertTrue(isset($results[0]->contact->type));
        $this->assertTrue(isset($results[0]->contacts[0]->type));
        $this->assertTrue(isset($results[0]->contacts[1]->type));
        $this->assertTrue(isset($results[0]->contacts[2]->type));

        $this->assertTrue(isset($results[1]->contact->type));
        $this->assertTrue(isset($results[1]->contacts[0]->type));
        $this->assertTrue(isset($results[1]->contacts[1]->type));
        $this->assertTrue(isset($results[1]->contacts[2]->type));
    }
}
