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
     * @const vendorized country mime type for countries
     */
    const CONTENT_TYPE = 'application/vnd.graviton.taxonomy.country+json; charset=UTF-8';
    /**
     * @const corresponding vendorized schema mime type
     */
    const SCHEMA_TYPE = 'application/vnd.graviton.schema.taxonomy.country+json';
    /**
     * @const corresponding vendorized collection schema mime type
     */
    const COLLECTION_SCHEMA_TYPE = 'application/vnd.graviton.schema.collection+json';

    /**
     * setup client and load fixtures
     *
     * @return void
     */
    public function setUp()
    {
        $this->loadFixtures(
            array(
                'Graviton\TaxonomyBundle\DataFixtures\MongoDB\LoadCountryData'
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
        $results = $client->getResults();

        $this->assertResponseContentType(self::COLLECTION_SCHEMA_TYPE.'; charset=UTF-8', $response);

        $this->assertContains(
            '<http://localhost/taxonomy/country>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );
        $this->assertContains(
            '<http://localhost/schema/schema/collection>; rel="schema"; type="'.self::COLLECTION_SCHEMA_TYPE.'"',
            explode(',', $response->headers->get('Link'))
        );

        $this->markTestIncomplete();
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
        $this->assertContains(
            '<http://localhost/schema/taxonomy/country>; rel="schema"; type="'.self::SCHEMA_TYPE.'"',
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
        $this->markTestIncomplete();
    }

    /**
     * test that changing countries is forbidden
     *
     * @return void
     */
    public function testPutApp()
    {
        $this->markTestIncomplete();
    }

    /**
     * test that deleting a country is forbidden
     *
     * @return void
     */
    public function testDeleteApp()
    {
        $this->markTestIncomplete();
    }

    /**
     * test getting schema information
     *
     * @return void
     */
    public function testGetCountrySchemaInformation()
    {
        $client = static::createRestClient();

        $client->request('GET', '/schema/taxonomy/country');

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::SCHEMA_TYPE.'; charset=UTF-8', $response);

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals('Country', $results->title);
        $this->assertEquals('A country record.', $results->description);
        $this->assertEquals('object', $results->type);

        $fieldAssertions = array(
            'id' => array('description' => 'ISO 3166-1 alpha-3 code.'),
            'name' => array('description' => 'Country name.'),
            'isoCode' => array('description' => 'ISO 3166-1 alpha-2 code (aka cTLD).'),
            'capitalCity' => array('description' => 'Capital city.'),
            'longitude' => array('description' => 'W/O geographic coordinate.'),
            'latitude' => array('description' => 'N/S geographic coordinate.')
        );
        foreach ($fieldAssertions as $field => $values) {
            $this->assertEquals('string', $results->properties->$field->type);
            $this->assertEquals($values['description'], $results->properties->$field->description);
        }

        $this->assertContains('id', $results->required);
        $this->assertContains('name', $results->required);
        $this->assertContains('isoCode', $results->required);
        $this->assertNotContains('capitalCity', $results->required);
        $this->assertNotContains('latitude', $results->required);
        $this->assertNotContains('longitude', $results->required);

    }
}
