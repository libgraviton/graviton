<?php

namespace Graviton\EntityBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;

/**
 * Basic functional tests for /entity/country.
 *
 * @category GravitonEntityBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @author   Dario Nuevo <Dario.Nuevo@swisscom.com>
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class CountryControllerTest extends RestTestCase
{
    /**
     * @const complete content type string expected on a resouce
     */
    const CONTENT_TYPE = 'application/json; charset=UTF-8; profile=http://localhost/schema/entity/country/item';

    /**
     * @const corresponding vendorized collection schema mime type
     */
    const COL_TYPE = 'application/json; charset=UTF-8; profile=http://localhost/schema/entity/country/collection';

    /**
     * setup client and load fixtures
     *
     * @return void
     */
    public function setUp()
    {
        $this->loadFixtures(
            array(
                'Graviton\EntityBundle\DataFixtures\MongoDB\LoadCountryData',
                'Graviton\I18nBundle\DataFixtures\MongoDB\LoadLanguageData'
            ),
            null,
            'doctrine_mongodb'
        );
    }

    /**
     * Validates that every request contains an appropiate link header
     *
     * @dataProvider queryStringProvider
     * @return void
     */
    public function testFindAllWithPaging($queryString, $pageNumber)
    {
        $client = static::createRestClient();
        $client->request('GET', '/entity/country'. $queryString);

        $response = $client->getResponse();
        $linkHeader = explode(',', $response->headers->get('Link'));

        $this->assertResponseContentType(self::COL_TYPE, $response);

        $this->assertContains(
            '<http://localhost/entity/country?page='. $pageNumber .'&per_page=10>; rel="self"',
            $linkHeader
        );
        $this->assertContains(
            '<http://localhost/entity/country?page='. ($pageNumber - 1) .'&per_page=10>; rel="prev"',
            $linkHeader
        );
        $this->assertContains(
            '<http://localhost/entity/country?page='. ($pageNumber + 1) .'&per_page=10>; rel="next"',
            $linkHeader
        );
    }

    /**
     * Test data provider for the testFindAllWithPaging()
     *
     * @return array
     */
    public function queryStringProvider()
    {
        return array(
            'get 2nd page' => array('?page=2', 2),
            'get 15th page' => array('?page=15', 15),
            'get 25th page' => array('?page=25', 25),
        );
    }

    /**
     * Validates that a request providing the first 10 entities contains an appropriate link header
     *
     * @return void
     */
    public function testFindAllWithPagingFirstPage()
    {
        $client = static::createRestClient();
        $client->request('GET', '/entity/country');

        $response = $client->getResponse();
        $linkHeader = explode(',', $response->headers->get('Link'));

        $this->assertResponseContentType(self::COL_TYPE, $response);

        $this->assertContains(
            '<http://localhost/entity/country?page=26&per_page=10>; rel="last"',
            $linkHeader
        );
        $this->assertContains(
            '<http://localhost/entity/country?page=1&per_page=10>; rel="self"',
            $linkHeader
        );
        $this->assertContains(
            '<http://localhost/entity/country?page=2&per_page=10>; rel="next"',
            $linkHeader
        );
    }

    /**
     * Validates that a request providing the last 10 entities contains an appropriate link header
     *
     * @return void
     */
    public function testFindAllWithPagingLastPage()
    {
        $client = static::createRestClient();
        $client->request('GET', '/entity/country?page=26');

        $response = $client->getResponse();

        $this->assertResponseContentType(self::COL_TYPE, $response);

        $this->assertContains(
            '<http://localhost/entity/country?page=26&per_page=10>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );
        $this->assertContains(
            '<http://localhost/entity/country?page=1&per_page=10>; rel="first"',
            explode(',', $response->headers->get('Link'))
        );
    }


    /**
     * check if all fixtures are returned on GET
     *
     * @return void
     */
    public function testFindAllPlainRequest()
    {
        $client = static::createRestClient();
        $client->request('GET', '/entity/country');

        $response = $client->getResponse();

        $this->assertResponseContentType(self::COL_TYPE, $response);

        $this->assertContains(
            '<http://localhost/entity/country?page=1&per_page=10>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );
        $this->assertContains(
            '<http://localhost/entity/country?page=2&per_page=10>; rel="next"',
            explode(',', $response->headers->get('Link'))
        );
        $this->assertContains(
            '<http://localhost/entity/country?page=26&per_page=10>; rel="last"',
            explode(',', $response->headers->get('Link'))
        );
    }

    /**
     * check if per_page param works on collections
     *
     * @return void
     */
    public function testFindAllWithPerPageParam()
    {
        $client = static::createRestClient();
        $client->request('GET', '/entity/country?per_page=20');

        $response = $client->getResponse();

        $this->assertResponseContentType(self::COL_TYPE, $response);

        $this->assertEquals(20, count($client->getResults()));

        $this->assertContains(
            '<http://localhost/entity/country?page=1&per_page=20>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );
        $this->assertContains(
            '<http://localhost/entity/country?page=2&per_page=20>; rel="next"',
            explode(',', $response->headers->get('Link'))
        );
        $this->assertContains(
            '<http://localhost/entity/country?page=13&per_page=20>; rel="last"',
            explode(',', $response->headers->get('Link'))
        );
    }

    /**
     * test if we can get an country by id
     *
     * @return void
     */
    public function testGetApp()
    {
        $client = static::createRestClient();
        $client->request('GET', '/entity/country/CHE');
        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::CONTENT_TYPE, $response);

        $this->assertEquals('CHE', $results->id);
        $this->assertEquals('CH', $results->isoCode);
        $this->assertEquals('Switzerland', $results->name);
        $this->assertEquals('Bern', $results->capitalCity);
        $this->assertEquals('7.44821', $results->longitude);
        $this->assertEquals('46.948', $results->latitude);

        $this->assertContains(
            '<http://localhost/entity/country/CHE>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );
    }

    /**
     * test if creating countries is forbidden
     *
     * @return void
     */
    public function testPostApp()
    {
        $testCountry = new \stdClass;
        $testCountry->id = 'LSB';
        $testCountry->name = 'Independent Federation of Lucas';
        $testCountry->isoCode = 'LB';
        $testCountry->capitalCity = 'Muesmatt';
        $testCountry->longitude = 1;
        $testCountry->latitude = 1;

        $client = static::createRestClient();
        $client->post('/entity/country', $testCountry);

        $response = $client->getResponse();

        $this->assertEquals(405, $response->getStatusCode());
        $this->assertEquals('GET, HEAD, OPTIONS', $response->headers->get('Allow'));
    }

    /**
     * test that changing countries is forbidden
     *
     * @return void
     */
    public function testPutApp()
    {
        $client = static::createRestClient();
        $this->assertPutFails('/entity/country/CHE', $client);
    }

    /**
     * test that deleting a country is forbidden
     *
     * @return void
     */
    public function testDeleteApp()
    {
        $client = static::createRestClient();
        $client->request('DELETE', '/entity/country/CHE');

        $this->assertEquals(405, $client->getResponse()->getStatusCode());
        $this->assertEquals('GET, HEAD, OPTIONS', $client->getResponse()->headers->get('Allow'));
    }

    /**
     * test getting schema information
     *
     * @return void
     */
    public function testGetCountrySchemaInformation()
    {
        $client = static::createRestClient();

        $client->request('OPTIONS', '/entity/country/CHE');

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType('application/schema+json', $response);

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals('Country', $results->title->en);
        $this->assertEquals('A country record.', $results->description->en);
        $this->assertEquals('object', $results->type);

        $fieldAssertions = array(
            'id' => array(
                'title' => 'ID',
                'description' => 'ISO 3166-1 alpha-3 code.'
            ),
            'name' => array(
                'title' => 'Name',
                'description' => 'Country name.'
            ),
            'isoCode' => array(
                'title' => 'ISO Code',
                'description' => 'ISO 3166-1 alpha-2 code (aka cTLD).'
            ),
            'capitalCity' => array(
                'title' => 'Capital',
                'description' => 'Capital city.'
            ),
            'longitude' => array(
                'title' => 'Longitude',
                'description' => 'W/E geographic coordinate.'
            ),
            'latitude' => array(
                'title' => 'Latitude',
                'description' => 'N/S geographic coordinate.'
            )
        );
        foreach ($fieldAssertions as $field => $values) {
            $this->assertEquals('string', $results->properties->$field->type);
            $this->assertEquals($values['description'], $results->properties->$field->description->en);
            $this->assertEquals($values['title'], $results->properties->$field->title->en);
        }

        $this->assertContains('id', $results->required);
        $this->assertContains('name', $results->required);
        $this->assertContains('isoCode', $results->required);
        $this->assertNotContains('capitalCity', $results->required);
        $this->assertNotContains('latitude', $results->required);
        $this->assertNotContains('longitude', $results->required);
        $this->assertCorsHeaders('GET, OPTIONS', $response);
    }
}
