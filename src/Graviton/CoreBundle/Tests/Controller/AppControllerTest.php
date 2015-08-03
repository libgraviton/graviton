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
                'Graviton\CoreBundle\DataFixtures\MongoDB\LoadAppData',
                'Graviton\I18nBundle\DataFixtures\MongoDB\LoadLanguageData',
                'Graviton\I18nBundle\DataFixtures\MongoDB\LoadMultiLanguageData',
                'Graviton\I18nBundle\DataFixtures\MongoDB\LoadTranslatableData',
                'Graviton\I18nBundle\DataFixtures\MongoDB\LoadTranslatablesApp'
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

        $this->assertEquals('admin', $results[0]->id);
        $this->assertEquals('Administration', $results[0]->title->en);
        $this->assertEquals(true, $results[0]->showInMenu);
        $this->assertEquals(2, $results[0]->order);

        $this->assertEquals('tablet', $results[1]->id);
        $this->assertEquals('Tablet', $results[1]->title->en);
        $this->assertEquals(true, $results[1]->showInMenu);
        $this->assertEquals(1, $results[1]->order);

        $this->assertContains(
            '<http://localhost/core/app>; rel="self"',
            $response->headers->get('Link')
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
        $_SERVER['QUERY_STRING'] = 'perPage=1&q=eq(showInMenu,true)';
        $client->request('GET', '/core/app?perPage=1&q=eq(showInMenu,true)');
        unset($_SERVER['QUERY_STRING']);
        $response = $client->getResponse();

        $this->assertEquals(1, count($client->getResults()));

        $this->assertContains(
            '<http://localhost/core/app?q=eq(showInMenu%2Ctrue)>; rel="self"',
            $response->headers->get('Link')
        );

        $this->assertContains(
            '<http://localhost/core/app?q=eq(showInMenu%2Ctrue)&page=2&perPage=1>; rel="next"',
            $response->headers->get('Link')
        );

        $this->assertContains(
            '<http://localhost/core/app?q=eq(showInMenu%2Ctrue)&page=2&perPage=1>; rel="last"',
            $response->headers->get('Link')
        );

    }

    /**
     * rql limit() should be *never* overwritten by $_GET['perPage'] *or* default value
     *
     * @return void
     */
    public function testGetAppPagingWithRql()
    {
        // does limit work?
        $client = static::createRestClient();
        $client->request('GET', '/core/app?q=limit(1)');
        $this->assertEquals(1, count($client->getResults()));

        // rql before GET?
        $client = static::createRestClient();
        $client->request('GET', '/core/app?perPage=2&q=limit(1)');
        $this->assertEquals(1, count($client->getResults()));

        $response = $client->getResponse();

        $this->assertContains(
            '<http://localhost/core/app?q=limit(1)>; rel="self"',
            $response->headers->get('Link')
        );

        $this->assertContains(
            '<http://localhost/core/app?q=limit(1)&page=2&perPage=1>; rel="next"',
            $response->headers->get('Link')
        );

        $this->assertContains(
            '<http://localhost/core/app?q=limit(1)&page=2&perPage=1>; rel="last"',
            $response->headers->get('Link')
        );

        // "page" override - rql before get
        $client = static::createRestClient();
        $client->request('GET', '/core/app?perPage=2&page=1&q=limit(1,1)');
        $this->assertEquals(1, count($client->getResults()));

        $response = $client->getResponse();

        $this->assertContains(
            '<http://localhost/core/app?q=limit(1%2C1)>; rel="self"',
            $response->headers->get('Link')
        );

        // we're passing page=1, but should be on last.. so next and last should be identical
        $nextAndLastUrl = 'http://localhost/core/app?q=limit(1%2C1)&page=2&perPage=1';

        $this->assertContains(
            '<'.$nextAndLastUrl.'>; rel="next"',
            $response->headers->get('Link')
        );

        $this->assertContains(
            '<'.$nextAndLastUrl.'>; rel="last"',
            $response->headers->get('Link')
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
            $response->headers->get('Link')
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

        $client = static::createRestClient();
        $client->post('/core/app', $testApp);
        $response = $client->getResponse();
        $results = $client->getResults();

        // we sent a location header so we don't want a body
        $this->assertNull($results);
        $this->assertContains('/core/app', $response->headers->get('Location'));

        $client = static::createRestClient();
        $client->request('GET', $response->headers->get('Location'));
        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::CONTENT_TYPE, $response);
        $this->assertEquals('new Test App', $results->title->en);
        $this->assertTrue($results->showInMenu);
        $this->assertContains(
            '<http://localhost/core/app/'.$results->id.'>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );
    }

    /**
     * test if we get a correct return if we post empty.
     *
     * @return void
     */
    public function testPostEmptyApp()
    {
        $client = static::createRestClient();

        // send nothing really..
        $client->post('/core/app', "", array(), array(), array(), false);

        $response = $client->getResponse();

        $this->assertContains(
            'No input data',
            $response->getContent()
        );

        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * test if 500 error is reported when posting an malformed input
     *
     * @return void
     */
    public function testPostMalformedApp()
    {
        $testApp = new \stdClass;
        $testApp->title = new \stdClass;
        $testApp->title->en = 'new Test App';
        $testApp->showInMenu = true;

        // malform it ;-)
        $input = str_replace(":", ";", json_encode($testApp));

        $client = static::createRestClient();

        // make sure this is sent as 'raw' input (not json_encoded again)
        $client->post('/core/app', $input, array(), array(), array(), false);

        $response = $client->getResponse();

        $content = $response->getContent();
        $this->assertContains(
            'syntax error',
            strtolower($content)
        );
        $this->assertContains(
            'malformed JSON',
            $content
        );

        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Tests if an error is returned when an id is send in a post
     *
     * @return void
     */
    public function testPostWithId()
    {
        $helloApp = new \stdClass();
        $helloApp->id = 101;
        $helloApp->name = "tubel";

        $client = static::createRestClient();
        $client->post('/person/customer', $helloApp);

        $this->assertEquals(
            'Can not be given on a POST request. Do a PUT request instead to update an existing record.',
            $client->getResults()[0]->message
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

        $client = static::createRestClient();
        $client->put('/core/app/tablet', $helloApp);

        $this->assertNull($client->getResults());
        $this->assertNull($client->getResponse()->headers->get('Location'));

        $client = static::createRestClient();
        $client->request('GET', '/core/app/tablet');
        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::CONTENT_TYPE, $response);
        $this->assertEquals('Tablet', $results->title->en);
        $this->assertFalse($results->showInMenu);
        $this->assertContains(
            '<http://localhost/core/app/tablet>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );

    }

    /**
     * Try to update an app with a non matching ID in GET and req body
     *
     * @return void
     */
    public function testNonMatchingIdPutApp()
    {
        $helloApp = new \stdClass();
        $helloApp->id = "tablet";
        $helloApp->title = new \stdClass();
        $helloApp->title->en = "Tablet";
        $helloApp->showInMenu = false;

        $client = static::createRestClient();
        $client->put('/core/app/someotherapp', $helloApp);

        $response = $client->getResponse();

        $this->assertContains(
            'Record ID in your payload must be the same',
            $response->getContent()
        );

        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * We had an issue when PUTing without ID would create a new record.
     * This test ensures that we don't do that, instead we should apply the ID from the GET req.
     *
     * @return void
     */
    public function testPutAppNoIdInPayload()
    {
        $helloApp = new \stdClass();
        $helloApp->title = new \stdClass();
        $helloApp->title->en = 'New tablet';
        $helloApp->showInMenu = false;

        $client = static::createRestClient();
        $client->put('/core/app/tablet', $helloApp);

        // we sent a location header so we don't want a body
        $this->assertNull($client->getResults());

        $client = static::createRestClient();
        $client->request('GET', '/core/app/tablet');
        $results = $client->getResults();

        $this->assertEquals('tablet', $results->id);
        $this->assertEquals('New tablet', $results->title->en);
        $this->assertFalse($results->showInMenu);
    }

    /**
     * test updating an inexistant document (upsert)
     *
     * @return void
     */
    public function testUpsertApp()
    {
        $isnogudApp = new \stdClass;
        $isnogudApp->id = 'isnogud';
        $isnogudApp->title = new \stdClass;
        $isnogudApp->title->en = 'I don\'t exist';

        $client = static::createRestClient();
        $client->put('/core/app/isnogud', $isnogudApp);

        $this->assertEquals(204, $client->getResponse()->getStatusCode());
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
        $helloApp->showInMenu = [];

        $client = static::createRestClient();
        $client->put('/core/app/tablet', $helloApp);

        $results = $client->getResults();

        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        $this->assertContains('showInMenu', $results[0]->propertyPath);
        $this->assertEquals('This value is not valid.', $results[0]->message);
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

        $this->assertCorsHeaders('GET, POST, PUT, DELETE, OPTIONS', $response);
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

        $client->request('GET', '/schema/core/app/collection');

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
            '<http://localhost/schema/core/app/collection>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );
    }

    /**
     * Test for searchable translations
     *
     * @return void
     */
    public function testSearchableTranslations()
    {
        $serverParams = array('HTTP_ACCEPT_LANGUAGE' => 'en, de');

        // fetch app
        $client = static::createRestClient();
        $client->request('GET', '/core/app/admin', array(), array(), $serverParams);

        // add german title
        $app = $client->getResults();
        $app->title->de = 'Die Administration';

        $client = static::createRestClient();
        $client->put('/core/app/admin', $app, array(), array(), $serverParams);

        // get our 'ref app' for admin
        $client = static::createRestClient();
        $client->request('GET', '/core/app/admin', array(), array(), $serverParams);
        $refApp = $client->getResults();

        // ok - now let's search for that
        // this should all find the same document
        $expressions = [
            'eq(title.de,Die%20Administration)',
            'eq(title.en,Administration)',
            'eq(title,Administration)',
            'like(title.de,*Administr*)'
        ];

        foreach ($expressions as $expr) {
            $client = static::createRestClient();
            $_SERVER['QUERY_STRING'] = 'q='.$expr;
            $client->request(
                'GET',
                '/core/app?q='.$expr,
                array(
                    //'q' => $expr
                ),
                array(),
                $serverParams
            );
            unset($_SERVER['QUERY_STRING']);

            $result = $client->getResults();
            $this->assertCount(1, $result);
            $this->assertEquals($refApp, $result[0]);
        }

        /**
         * now we test an edge case. at this point we have 'Die Administration' for the first app
         * translated. but "Tablet" from the second app is not localized yet - but it is present in
         * the output. so a search for "title.de" with "*a*" should find both apps!
         */
        $client = static::createRestClient();
        $client->request(
            'GET',
            '/core/app',
            array(
                'q' => 'like(title.de,*a*)'
            ),
            array(),
            $serverParams
        );

        $result = $client->getResults();
        $this->assertCount(2, $result);
    }

    /**
     * ensure we have nice parse error output in rql parse failure
     *
     * @return void
     */
    public function testRqlSyntaxError()
    {
        $client = static::createRestClient();

        $client->request('GET', '/core/app?q=eq(name)');

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertEquals(400, $response->getStatusCode());

        $this->assertContains('syntax error in rql: ', $results->message);
        $this->assertContains('Unexpected token', $results->message);
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
        $this->assertEquals('translatable', $schema->properties->title->format);
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
