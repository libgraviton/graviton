<?php
/**
 * functional test for /core/app
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;

/**
 * Basic functional test for /core/app.
 *
 * @category GravitonCoreBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class AppControllerTest extends RestTestCase
{
    /**
     * @const vendorized app mime type for app data
     */
    const CONTENT_TYPE = 'application/vnd.graviton.core.app+json; charset=UTF-8';
    /**
     * @const corresponding vendorized schema mime type
     */
    const SCHEMA_TYPE = 'application/vnd.graviton.schema.core.app+json';
    /**
     * @const corresponding vendorized schema mime type
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
                'Graviton\CoreBundle\DataFixtures\MongoDB\LoadAppData'
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
        $client->request('GET', '/core/app');

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::COLLECTION_SCHEMA_TYPE.'; charset=UTF-8', $response);

        $this->assertEquals(2, count($results));

        $this->assertEquals('hello', $results[0]->id);
        $this->assertEquals('Hello World!', $results[0]->title);
        $this->assertEquals(true, $results[0]->showInMenu);

        $this->assertEquals('admin', $results[1]->id);
        $this->assertEquals('Administration', $results[1]->title);
        $this->assertEquals(true, $results[1]->showInMenu);

        $this->assertContains(
            '<http://localhost/core/app>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );
        $this->assertContains(
            '<http://localhost/schema/schema/collection>; rel="schema"; type="'.self::COLLECTION_SCHEMA_TYPE.'"',
            explode(',', $response->headers->get('Link'))
        );
    }

    /**
     * test if we can get an app by id
     *
     * @return void
     */
    public function testGetApp()
    {
        $client = static::createRestClient();
        $client->request('GET', '/core/app/admin');
        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::CONTENT_TYPE, $response);

        $this->assertEquals('admin', $results->id);
        $this->assertEquals('Administration', $results->title);
        $this->assertEquals(true, $results->showInMenu);

        $this->assertContains(
            '<http://localhost/core/app/admin>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );
        $this->assertContains(
            '<http://localhost/schema/core/app>; rel="schema"; type="'.self::SCHEMA_TYPE.'"',
            explode(',', $response->headers->get('Link'))
        );
    }

    /**
     * test if we can create an app through POST
     *
     * @return void
     */
    public function testPostApp()
    {
        $testApp = new \stdClass;
        $testApp->id = 'new';
        $testApp->title = 'new Test App';
        $testApp->showInMenu = true;

        $client = static::createRestClient();
        $client->post('/core/app', $testApp);

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::CONTENT_TYPE, $response);

        $this->assertEquals('new', $results->id);
        $this->assertEquals('new Test App', $results->title);
        $this->assertTrue($results->showInMenu);

        $this->assertContains(
            '<http://localhost/core/app/new>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );
        $this->assertContains(
            '<http://localhost/schema/core/app>; rel="schema"; type="'.self::SCHEMA_TYPE.'"',
            explode(',', $response->headers->get('Link'))
        );
    }

    /**
     * test updating apps
     *
     * @return void
     */
    public function testPutApp()
    {
        $client = static::createRestClient();
        $client->request('GET', '/core/app/hello');

        $helloApp = $client->getResults();
        $helloApp->showInMenu = false;

        $client->put('/core/app/hello', $helloApp);

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::CONTENT_TYPE, $response);

        $this->assertEquals('hello', $results->id);
        $this->assertEquals('Hello World!', $results->title);
        $this->assertFalse($results->showInMenu);

        $this->assertContains(
            '<http://localhost/core/app/hello>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );
        $this->assertContains(
            '<http://localhost/schema/core/app>; rel="schema"; type="'.self::SCHEMA_TYPE.'"',
            explode(',', $response->headers->get('Link'))
        );
    }

    /**
     * test updating an inexistant document
     *
     * @return void
     */
    public function testPutInexistantApp()
    {
        $isnogudApp = new \stdClass;
        $isnogudApp->id = 'isnogud';
        $isnogudApp->title = 'I don\'t exist';

        $client = static::createRestClient();
        $client->put('/core/app/isnogud', $isnogudApp);

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    /**
     * test deleting an app
     *
     * @return void
     */
    public function testDeleteApp()
    {
        $testApp = new \stdClass;
        $testApp->id = 'hello';
        $testApp->title = 'Hello World!';
        $testApp->showInMenu = true;

        $client = static::createRestClient();
        $client->request('DELETE', '/core/app/hello');

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::CONTENT_TYPE, $response);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * test failing validation on boolean field
     *
     * @return void
     */
    public function testFailingBooleanValidationOnAppUpdate()
    {
        $helloApp = new \stdClass;
        $helloApp->id = 'hello';
        $helloApp->title = 'Hello World!';
        $helloApp->showInMenu = 'I am a string and not a boolean.';

        $client = static::createRestClient();
        $client->put('/core/app/hello', $helloApp);

        // mark as incomplete early to shift the focus away from validation for now
        $this->markTestIncomplete('mongodb validation needs better testing and implementing');

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    /**
     * test getting schema information
     *
     * @return void
     */
    public function testGetSchemaInformation()
    {
        $client = static::createRestClient();

        $client->request('GET', '/schema/core/app');

        $response = $client->getResponse();
        $results = $client->getResults();

        echo $results->message.PHP_EOL;
        var_dump($results->file);

        $this->assertResponseContentType(self::SCHEMA_TYPE.'; charset=UTF-8', $response);

        $this->markTestIncomplete('Schema response needs extensive testing');
    }
}
