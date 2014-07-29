<?php

namespace Graviton\TaxonomyBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;

/**
 * Basic functional tests for /taxonomy/country.
 *
 * @category GravitonTaxonomyBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class CountryControllerTest extends RestTestCase
{
    /**
     * @const complete content type string expected on a resouce
     */
    const CONTENT_TYPE = 'application/json; charset=UTF-8; profile=http://localhost/schema/taxonomy/country/item';

    /**
     * @const corresponding vendorized collection schema mime type
     */
    const COL_TYPE = 'application/json; charset=UTF-8; profile=http://localhost/schema/taxonomy/country/collection';

    /**
     * setup client and load fixtures
     *
     * @return void
     */
    public function setUp()
    {
        $this->loadFixtures(
            array(
                'Graviton\TaxonomyBundle\DataFixtures\MongoDB\LoadCountryData',
                'Graviton\I18nBundle\DataFixtures\MongoDB\LoadLanguageData'
            ),
            null,
            'doctrine_mongodb'
        );
    }

    /**
     * check if all fixtures are returned on GET
     *
     * @return void
     */
    public function testFindAll()
    {
        $client = static::createRestClient();
        $client->request('GET', '/taxonomy/country');

        $response = $client->getResponse();

        $this->assertResponseContentType(self::COL_TYPE, $response);

        $this->assertContains(
            '<http://localhost/taxonomy/country?page=1>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );
        $this->assertContains(
            '<http://localhost/taxonomy/country?page=2>; rel="next"',
            explode(',', $response->headers->get('Link'))
        );
        $this->assertContains(
            '<http://localhost/taxonomy/country?page=26>; rel="last"',
            explode(',', $response->headers->get('Link'))
        );

        $client->request('GET', '/taxonomy/country?page=2');

        $response = $client->getResponse();

        $this->assertResponseContentType(self::COL_TYPE, $response);

        $this->assertContains(
            '<http://localhost/taxonomy/country?page=2>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );
        $this->assertContains(
            '<http://localhost/taxonomy/country?page=1>; rel="prev"',
            explode(',', $response->headers->get('Link'))
        );
        $this->assertContains(
            '<http://localhost/taxonomy/country?page=3>; rel="next"',
            explode(',', $response->headers->get('Link'))
        );

        $client->request('GET', '/taxonomy/country?page=26');

        $response = $client->getResponse();

        $this->assertResponseContentType(self::COL_TYPE, $response);

        $this->assertContains(
            '<http://localhost/taxonomy/country?page=26>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );
        $this->assertContains(
            '<http://localhost/taxonomy/country?page=1>; rel="first"',
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
        $client->request('GET', '/taxonomy/country/CHE');
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
            '<http://localhost/taxonomy/country/CHE>; rel="self"',
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
        $client->post('/taxonomy/country', $testCountry);

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
        $client->request('GET', '/taxonomy/country/CHE');

        $country = $client->getResults();

        $client->put('/taxonomy/country/CHE', $country);

        $response = $client->getResponse();

        $this->assertEquals(405, $response->getStatusCode());
        $this->assertEquals('GET, HEAD, OPTIONS', $response->headers->get('Allow'));
    }

    /**
     * test that deleting a country is forbidden
     *
     * @return void
     */
    public function testDeleteApp()
    {
        $client = static::createRestClient();
        $client->request('DELETE', '/taxonomy/country/CHE');

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

        $client->request('OPTIONS', '/taxonomy/country/CHE');

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
        $this->assertEquals('*', $response->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals('GET, OPTIONS', $response->headers->get('Access-Control-Allow-Methods'));
    }
}
