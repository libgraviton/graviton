<?php
/**
 * functional test for /core/app
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;

/**
 * Basic functional test for /core/app.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class AppControllerTest extends RestTestCase
{
    /**
     * @const complete content type string expected on a resouce
     */
    const CONTENT_TYPE = 'application/json; charset=UTF-8; profile=http://localhost/schema/core/app/item';

    /**
     * @const corresponding vendorized schema mime type
     */
    const COLLECTION_TYPE = 'application/json; charset=UTF-8; profile=http://localhost/schema/core/app/collection';

    /**
     * setup client and load fixtures
     *
     * @return void
     */
    public function setUp()
    {
        $this->loadFixtures(
            array(
                'GravitonDyn\AppBundle\DataFixtures\MongoDB\LoadAppData',
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
        $client->request('GET', '/core/app');

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::COLLECTION_TYPE, $response);

        $this->assertEquals(2, count($results));

        $this->assertEquals('tablet', $results[0]->id);
        $this->assertEquals('Tablet', $results[0]->title->en);
        $this->assertEquals(true, $results[0]->showInMenu);
        $this->assertEquals(1, $results[0]->order);

        $this->assertEquals('admin', $results[1]->id);
        $this->assertEquals('Administration', $results[1]->title->en);
        $this->assertEquals(true, $results[1]->showInMenu);
        $this->assertEquals(2, $results[1]->order);

        $this->assertContains(
            '<http://localhost/core/app>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );
        $this->assertEquals('*', $response->headers->get('Access-Control-Allow-Origin'));
    }

    /**
     * test if we can get list of apps, paged and with filters..
     *
     * @return void
     */
    public function testGetAppWithFilteringAndPaging()
    {
        $client = static::createRestClient();
        $client->request('GET', '/core/app?perPage=1&q='.urlencode('eq(showInMenu,true)'));
        $response = $client->getResponse();

        $this->assertEquals(1, count($client->getResults()));

        $this->assertContains(
            '<http://localhost/core/app?q=eq%28showInMenu%2Ctrue%29>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );

        $this->assertContains(
            '<http://localhost/core/app?q=eq%28showInMenu%2Ctrue%29&page=2&perPage=1>; rel="next"',
            explode(',', $response->headers->get('Link'))
        );

        $this->assertContains(
            '<http://localhost/core/app?q=eq%28showInMenu%2Ctrue%29&page=2&perPage=1>; rel="last"',
            explode(',', $response->headers->get('Link'))
        );

    }

    /**
     * check for empty collections when no fixtures are loaded
     *
     * @return void
     */
    public function testFindAllEmptyCollection()
    {
        // reset fixtures since we already have some from setUp
        $this->loadFixtures(array(), null, 'doctrine_mongodb');
        $client = static::createRestClient();
        $client->request('GET', '/core/app');

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::COLLECTION_TYPE, $response);

        $this->assertEquals(array(), $results);
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
        $this->assertEquals('Administration', $results->title->en);
        $this->assertEquals(true, $results->showInMenu);

        $this->assertContains(
            '<http://localhost/core/app/admin>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );
        $this->assertEquals('*', $response->headers->get('Access-Control-Allow-Origin'));
    }

    /**
     * test if we can create an app through POST
     *
     * @return void
     */
    public function testPostApp()
    {
        $testApp = new \stdClass;
        $testApp->title = new \stdClass;
        $testApp->title->en = 'new Test App';
        $testApp->showInMenu = true;
        $testApp->order = 3;

        $client = static::createRestClient();
        $client->post('/core/app', $testApp);

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::CONTENT_TYPE, $response);

        $this->assertEquals('new Test App', $results->title->en);
        $this->assertTrue($results->showInMenu);
        $this->assertEquals(3, $results->order);

        $this->assertContains(
            '<http://localhost/core/app/'.$results->id.'>; rel="self"',
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
        $helloApp = new \stdClass();
        $helloApp->id = "tablet";
        $helloApp->title = new \stdClass();
        $helloApp->title->en = "Tablet";
        $helloApp->showInMenu = false;
        $helloApp->order = 30;

        $client = static::createRestClient();
        $client->put('/core/app/tablet', $helloApp);

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::CONTENT_TYPE, $response);

        $this->assertEquals('tablet', $results->id);
        $this->assertEquals('Tablet', $results->title->en);
        $this->assertFalse($results->showInMenu);
        $this->assertEquals(30, $results->order);

        $this->assertContains(
            '<http://localhost/core/app/tablet>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );
        $this->assertEquals('*', $response->headers->get('Access-Control-Allow-Origin'));
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
        $isnogudApp->title = new \stdClass;
        $isnogudApp->title->en = 'I don\'t exist';
        $isnogudApp->showInMenu = true;
        $isnogudApp->order = 3;

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
        $testApp->id = 'tablet';
        $testApp->title = 'Tablet';
        $testApp->showInMenu = true;
        $testApp->order = 1;

        $client = static::createRestClient();
        $client->request('DELETE', '/core/app/tablet');

        $response = $client->getResponse();

        $this->assertResponseContentType(self::CONTENT_TYPE, $response);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('*', $response->headers->get('Access-Control-Allow-Origin'));

        $client->request('GET', '/core/app/tablet');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    /**
     * test failing validation on boolean field
     *
     * @return void
     */
    public function testFailingBooleanValidationOnAppUpdate()
    {
        $helloApp = new \stdClass;
        $helloApp->id = 'tablet';
        $helloApp->title = new \stdClass;
        $helloApp->title->en = 'Tablet';
        $helloApp->showInMenu = 'I am a string and not a boolean.';

        $client = static::createRestClient();
        $client->put('/core/app/tablet', $helloApp);

        $results = $client->getResults();

        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        $this->assertEquals('showInMenu', $results[0]->property_path);
        $this->assertEquals('The value "'.$helloApp->showInMenu.'" is not a valid boolean.', $results[0]->message);
    }

    /**
     * test getting schema information
     *
     * @return void
     */
    public function testGetAppSchemaInformation()
    {
        $client = static::createRestClient();
        $client->request('OPTIONS', '/core/app/hello');

        $response = $client->getResponse();

        $this->assertIsSchemaResponse($response);
        $this->assertIsAppSchema($client->getResults());
        $this->assertCorsHeaders('GET, POST, PUT, DELETE, OPTIONS', $response);

        $this->assertContains(
            '<http://localhost/schema/core/app/item>; rel="canonical"',
            explode(',', $response->headers->get('Link'))
        );
    }

    /**
     * test getting schema information from canonical url
     *
     * @return void
     */
    public function testGetAppSchemaInformationCanonical()
    {
        $client = static::createRestClient();
        $client->request('GET', '/schema/core/app/item');

        $this->assertIsSchemaResponse($client->getResponse());
        $this->assertIsAppSchema($client->getResults());
    }

    /**
     * test getting collection schema
     *
     * @return void
     */
    public function testGetAppCollectionSchemaInformation()
    {
        $client = static::createRestClient();

        $client->request('OPTIONS', '/core/app');

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType('application/schema+json', $response);
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals('Array of app objects', $results->title);
        $this->assertEquals('array', $results->type);
        $this->assertIsAppSchema($results->items);

        $this->assertEquals('*', $response->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals('GET, POST, PUT, DELETE, OPTIONS', $response->headers->get('Access-Control-Allow-Methods'));
        $this->assertContains(
            'Link',
            explode(',', $response->headers->get('Access-Control-Expose-Headers'))
        );

        $this->assertContains(
            '<http://localhost/schema/core/app/collection>; rel="canonical"',
            explode(',', $response->headers->get('Link'))
        );
    }

    /**
     * Test various permutations of the PATCH method
     *
     * @param string $patchString json-patch to apply
     *
     * @return void
     *
     * @dataProvider patchMethodTests
     */
    public function testPatchApp($patchString)
    {
        $client = static::createRestClient();
        $patchString = json_decode($patchString);

        $client->patch('/core/app/tablet', $patchString);

        $response = $client->getResponse();

        // client ha to be rebuild since the AppKernel will be resetted after a request
        // which will unregister bundles registered by bundle loader.
        $client = static::createRestClient();

        // get the patched record
        $client->request('GET', '/core/app/tablet');
        $results = $client->getResults();

        // check status code (204 No Content)
        $this->assertEquals('204', $response->getStatusCode());

        // check record values
        $this->assertEquals('tablet', $results->id);
        $this->assertEquals('Tablet', $results->title->en);
        $this->assertFalse($results->showInMenu);

        $this->assertContains(
            '<http://localhost/core/app/tablet>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );
    }

    /**
     * various ways to test PATH
     *
     * All of these should set showInMenu to false on the /core/app/hello record while never
     * creating any duplicate items.
     *
     * @return array
     */
    public function patchMethodTests()
    {
        return array(
            array('[{"op":"replace","path":"/showInMenu","value":false}]'),
            array('[{"op":"add","path":"/showInMenu","value":false}]'),
        );
    }

    /**
     * check if response looks like schema
     *
     * @param object $response response
     *
     * @return void
     */
    private function assertIsSchemaResponse($response)
    {
        $this->assertResponseContentType('application/schema+json', $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * check if a schema is of the app type
     *
     * @param \stdClass $schema schema from service to validate
     *
     * @return void
     */
    private function assertIsAppSchema(\stdClass $schema)
    {
        $this->assertEquals('App', $schema->title);
        $this->assertEquals('A graviton based app.', $schema->description);
        $this->assertEquals('object', $schema->type);

        $this->assertEquals('string', $schema->properties->id->type);
        $this->assertEquals('ID', $schema->properties->id->title);
        $this->assertEquals('Unique identifier for an app.', $schema->properties->id->description);
        $this->assertContains('id', $schema->required);

        $this->assertEquals('object', $schema->properties->title->type);
        $this->assertEquals('Title', $schema->properties->title->title);
        $this->assertEquals('Display name for an app.', $schema->properties->title->description);
        $this->assertEquals('string', $schema->properties->title->properties->en->type);
        $this->assertContains('title', $schema->required);

        $this->assertEquals('boolean', $schema->properties->showInMenu->type);
        $this->assertEquals('Show in Menu', $schema->properties->showInMenu->title);
        $this->assertEquals(
            'Define if an app should be exposed on the top level menu.',
            $schema->properties->showInMenu->description
        );
    }
}
