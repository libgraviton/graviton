<?php

namespace Graviton\PersonBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;

/**
 * Basic functional tests for /person/consultant.
 *
 * @category GravitonPersonBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class ConsultantControllerTest extends RestTestCase
{
    /**
     * @const complete content type string expected on a resouce
     */
    const CONTENT_TYPE = 'application/json; charset=UTF-8; profile=http://localhost/schema/person/consultant/item';

    /**
     * @const corresponding vendorized collection schema mime type
     */
    const COL_TYPE = 'application/json; charset=UTF-8; profile=http://localhost/schema/person/consultant/collection';

    /**
     * setup client and load fixtures
     *
     * @return void
     */
    public function setUp()
    {
        $this->loadFixtures(
            array(
                'Graviton\PersonBundle\DataFixtures\MongoDB\LoadConsultantData',
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
        $client->request('GET', '/person/consultant');

        $response = $client->getResponse();

        $this->assertResponseContentType(self::COL_TYPE, $response);

        $this->assertContains(
            '<http://localhost/person/consultant?page=1>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );
        $this->assertContains(
            '<http://localhost/person/consultant?page=2>; rel="next"',
            explode(',', $response->headers->get('Link'))
        );
        $this->assertContains(
            '<http://localhost/person/consultant?page=2>; rel="last"',
            explode(',', $response->headers->get('Link'))
        );

        $client->request('GET', '/person/consultant?page=2');

        $response = $client->getResponse();

        $this->assertResponseContentType(self::COL_TYPE, $response);

        $this->assertContains(
            '<http://localhost/person/consultant?page=2>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );
        $this->assertContains(
            '<http://localhost/person/consultant?page=1>; rel="prev"',
            explode(',', $response->headers->get('Link'))
        );
    }

    /**
     * test if we can get an consultant by id
     *
     * @return void
     */
    public function testGetConsultant()
    {
        $client = static::createRestClient();
        $client->request('GET', '/person/consultant/NOKB528VY');
        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::CONTENT_TYPE, $response);

        $this->assertEquals('NOKB528VY', $results->id);
        $this->assertEquals('Taylor', $results->firstName);
        $this->assertEquals('Hermiston', $results->lastName);

        $this->assertEquals('email', $results->contacts[0]->type);
        $this->assertEquals('mailto:brandyn60@littel.biz', $results->contacts[0]->uri);
        $this->assertEquals('phone', $results->contacts[1]->type);
        $this->assertEquals('tel:613-055-9515', $results->contacts[1]->uri);
        $this->assertEquals('fax', $results->contacts[2]->type);
        $this->assertEquals('tel:613-055-9515', $results->contacts[1]->uri);
        $this->assertEquals('web', $results->contacts[3]->type);
        $this->assertEquals('http://www.moore.net/', $results->contacts[3]->uri);

        $this->assertContains(
            '<http://localhost/person/consultant/NOKB528VY>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );
    }

    /**
     * test if creating consultants is forbidden
     *
     * @return void
     */
    public function testPostApp()
    {
        $testConsultant = new \stdClass;
        $testConsultant->id = 'HELLOTHERE';
        $testConsultant->firstName = 'Peter';
        $testConsultant->lastName = 'Smith';

        $client = static::createRestClient();
        $client->post('/person/consultant', $testConsultant);

        $response = $client->getResponse();

        $this->assertEquals(405, $response->getStatusCode());
        $this->assertEquals('GET, HEAD, OPTIONS', $response->headers->get('Allow'));
    }

    /**
     * test that changing consultants is forbidden
     *
     * @return void
     */
    public function testPutApp()
    {
        $client = static::createRestClient();
        $client->request('GET', '/person/consultant/NOKB528VY');

        $consultant = $client->getResults();

        $client->put('/person/consultant/NOKB528VY', $consultant);

        $response = $client->getResponse();

        $this->assertEquals(405, $response->getStatusCode());
        $this->assertEquals('GET, HEAD, OPTIONS', $response->headers->get('Allow'));
    }

    /**
     * test that deleting a consultant is forbidden
     *
     * @return void
     */
    public function testDeleteApp()
    {
        $client = static::createRestClient();
        $client->request('DELETE', '/person/consultant/NOKB528VY');

        $this->assertEquals(405, $client->getResponse()->getStatusCode());
        $this->assertEquals('GET, HEAD, OPTIONS', $client->getResponse()->headers->get('Allow'));
    }

    /**
     * test getting schema information
     *
     * @return void
     */
    public function testGetConsultantSchemaInformation()
    {
        $client = static::createRestClient();

        $client->request('GET', '/schema/person/consultant/item');

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType('application/schema+json', $response);

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals('Consultant', $results->title->en);
        $this->assertEquals('A customer consultant.', $results->description->en);
        $this->assertEquals('object', $results->type);

        $fieldAssertions = array(
            'id' => array(
                'title' => 'ID',
                'type' => 'string',
                'description' => 'Consultant identifier.'
            ),
            'firstName' => array(
                'title' => 'First Name',
                'type' => 'string',
                'description' => 'First name of consultant.'
            ),
            'lastName' => array(
                'title' => 'Last Name',
                'type' => 'string',
                'description' => 'Last name of consultant.'
            ),
            'title' => array(
                'title' => 'Title',
                'type' => 'string',
                'description' => 'Organizational title of consultant.'
            ),
            'contacts' => array(
                'title' => 'Contacts',
                'type' => 'array',
                'description' => 'Ways to contact a consultant.'
            )
        );
        foreach ($fieldAssertions as $field => $values) {
            $this->assertEquals($values['type'], $results->properties->$field->type);
            $this->assertEquals($values['description'], $results->properties->$field->description->en);
            $this->assertEquals($values['title'], $results->properties->$field->title->en);
        }

        $this->assertContains('id', $results->required);
        $this->assertContains('firstName', $results->required);
        $this->assertContains('lastName', $results->required);
        $this->assertNotContains('title', $results->required);
        $this->assertNotContains('contacts', $results->required);
        $this->assertEquals('*', $response->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals('GET, OPTIONS', $response->headers->get('Access-Control-Allow-Methods'));
    }
}
