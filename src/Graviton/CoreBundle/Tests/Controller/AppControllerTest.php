<?php
/**
 * functional test for /core/app
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\LinkHeaderParser\LinkHeader;
use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Basic functional test for /core/app.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class AppControllerTest extends RestTestCase
{
    /**
     * @const complete content type string expected on a resouce
     */
    const CONTENT_TYPE = 'application/json; charset=UTF-8';

    const SCHEMA_URL_ITEM = 'http://localhost/schema/core/app/item';

    const SCHEMA_URL_COLLECTION = 'http://localhost/schema/core/app/collection';

    /**
     * @var array fixtures
     */
    protected $standardFixtures = [
        'Graviton\CoreBundle\DataFixtures\MongoDB\LoadAppData',
        'Graviton\I18nBundle\DataFixtures\MongoDB\LoadLanguageData',
        'Graviton\I18nBundle\DataFixtures\MongoDB\LoadMultiLanguageData',
        'Graviton\I18nBundle\DataFixtures\MongoDB\LoadTranslationLanguageData',
        'Graviton\I18nBundle\DataFixtures\MongoDB\LoadTranslationAppData'
    ];

    /**
     * setup client and load fixtures
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->loadFixturesLocal($this->standardFixtures);
    }
    /**
     * check if all fixtures are returned on GET
     *
     * @return void
     */
    public function testFindAll()
    {
        $client = static::createRestClient();
        $client->request('GET', '/core/app/');

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::CONTENT_TYPE, $response);
        $this->assertResponseSchemaRel(self::SCHEMA_URL_COLLECTION, $response);
        $this->assertEquals(2, count($results));

        $this->assertEquals('admin', $results[0]->id);
        $this->assertEquals('Administration', $results[0]->name->en);
        $this->assertEquals(true, $results[0]->showInMenu);
        $this->assertEquals(2, $results[0]->order);

        $this->assertEquals('tablet', $results[1]->id);
        $this->assertEquals('Tablet', $results[1]->name->en);
        $this->assertEquals(true, $results[1]->showInMenu);
        $this->assertEquals(1, $results[1]->order);

        $linkHeader = LinkHeader::fromString($response->headers->get('Link'));
        $this->assertEquals('http://localhost/core/app/', $linkHeader->getRel('self')->getUri());

        $this->assertEquals('*', $response->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals('DENY', $response->headers->get('X-Frame-Options'));
    }

    /**
     * test that our paging headers are correct if client does NOT provide rql BUT
     * we have more records in the db then the default pagesize (= we will add limit() clauses to Link elements)
     *
     * @return void
     */
    public function testGeneratedPagingHeadersNoRql()
    {
        $this->loadFixturesLocal(
            [
                'Graviton\CoreBundle\DataFixtures\MongoDB\LoadAppDataExceedSinglePageLimit'
            ]
        );

        $client = static::createRestClient();
        $client->request('GET', '/core/app/');

        $response = $client->getResponse();
        $linkHeader = LinkHeader::fromString($response->headers->get('Link'));

        $this->assertEquals(
            'http://localhost/core/app/',
            $linkHeader->getRel('self')->getUri()
        );

        $this->assertEquals(
            'http://localhost/core/app/?limit(10,10)',
            $linkHeader->getRel('next')->getUri()
        );

        $this->assertEquals(
            'http://localhost/core/app/?limit(10,10)',
            $linkHeader->getRel('last')->getUri()
        );
    }

    /**
     * make sure that "-" can be unencoded in common rql strings as this is the expected behavior
     *
     * @return void
     */
    public function testLiteralDashInRql()
    {
        $this->loadFixturesLocal(
            [
                'Graviton\CoreBundle\DataFixtures\MongoDB\LoadAppDataExceedSinglePageLimit'
            ]
        );

        $client = static::createRestClient();
        $client->request('GET', '/core/app/?like(id,app-*)');

        $this->assertEquals(10, count($client->getResults()));
        $this->assertEquals("15", $client->getResponse()->headers->get('x-total-count'));

        $client = static::createRestClient();
        $client->request('GET', '/core/app/?eq(id,app-2)');

        $this->assertEquals(1, count($client->getResults()));

        // and encoded?
        $client = static::createRestClient();
        $client->request('GET', '/core/app/?eq(id,app%2D2)');

        $this->assertEquals(1, count($client->getResults()));
    }

    /**
     * test if we can get list of apps, paged and with filters..
     *
     * @return void
     */
    public function testGetAppWithFilteringAndPaging()
    {
        $this->loadFixturesLocal(
            array_merge(
                $this->standardFixtures,
                ['Graviton\CoreBundle\DataFixtures\MongoDB\LoadAppDataNoShowMenu']
            )
        );

        $client = static::createRestClient();
        $client->request('GET', '/core/app/?eq(showInMenu,true)&limit(1)');
        $response = $client->getResponse();

        $this->assertEquals(1, count($client->getResults()));

        $this->assertStringContainsString(
            '<http://localhost/core/app/?eq(showInMenu,true())&limit(1)>; rel="self"',
            $response->headers->get('Link')
        );

        $this->assertStringContainsString(
            '<http://localhost/core/app/?eq(showInMenu,true())&limit(1,1)>; rel="next"',
            $response->headers->get('Link')
        );

        $this->assertStringContainsString(
            '<http://localhost/core/app/?eq(showInMenu,true())&limit(1,1)>; rel="last"',
            $response->headers->get('Link')
        );

        // check for false
        $client = static::createRestClient();
        $client->request('GET', '/core/app/?eq(showInMenu,false)');
        $response = $client->getResponse();

        $this->assertEquals(2, count($client->getResults()));
        $this->assertStringContainsString(
            '<http://localhost/core/app/?eq(showInMenu,false())>; rel="self"',
            $response->headers->get('Link')
        );
    }

    /**
     * rql limit() should *never* be overwritten by default value
     *
     * @return void
     */
    public function testGetAppPagingWithRql()
    {
        // does limit work?
        $client = static::createRestClient();
        $client->request('GET', '/core/app/?limit(1)');
        $this->assertEquals(1, count($client->getResults()));

        $response = $client->getResponse();

        $this->assertStringContainsString(
            '<http://localhost/core/app/?limit(1)>; rel="self"',
            $response->headers->get('Link')
        );

        $this->assertStringContainsString(
            '<http://localhost/core/app/?limit(1,1)>; rel="next"',
            $response->headers->get('Link')
        );

        $this->assertStringContainsString(
            '<http://localhost/core/app/?limit(1,1)>; rel="last"',
            $response->headers->get('Link')
        );

        $this->assertSame('2', $response->headers->get('X-Total-Count'));
        $this->assertSame('1', $response->headers->get('X-Record-Count'));

        /*** pagination tests **/
        $client = static::createRestClient();
        $client->request('GET', '/core/app/?limit(1,1)');
        $this->assertEquals(1, count($client->getResults()));

        $response = $client->getResponse();

        $this->assertStringContainsString(
            '<http://localhost/core/app/?limit(1,1)>; rel="self"',
            $response->headers->get('Link')
        );

        $this->assertStringContainsString(
            '<http://localhost/core/app/?limit(1)>; rel="prev"',
            $response->headers->get('Link')
        );

        // we're on the 'last' page - so 'last' should not be in in Link header
        $this->assertStringNotContainsString(
            'rel="last"',
            $response->headers->get('Link')
        );

        $this->assertSame('2', $response->headers->get('X-Total-Count'));
        $this->assertSame('1', $response->headers->get('X-Record-Count'));

        /*** pagination with different rql test **/
        $client = static::createRestClient();
        $client->request('GET', '/core/app/?limit(1)&select(id)&sort(-order)');
        $this->assertEquals(1, count($client->getResults()));

        $response = $client->getResponse();

        $linkHeader = LinkHeader::fromString($response->headers->get('Link'));

        $this->assertEquals(
            'http://localhost/core/app/?select(id)&sort(-order)&limit(1)',
            $linkHeader->getRel('self')->getUri()
        );

        $this->assertEquals(
            'http://localhost/core/app/?select(id)&sort(-order)&limit(1,1)',
            $linkHeader->getRel('next')->getUri()
        );

        $this->assertEquals(
            'http://localhost/core/app/?select(id)&sort(-order)&limit(1,1)',
            $linkHeader->getRel('last')->getUri()
        );

        $this->assertStringNotContainsString(
            'rel="prev"',
            $response->headers->get('Link')
        );

        $this->assertSame('2', $response->headers->get('X-Total-Count'));
        $this->assertSame('1', $response->headers->get('X-Record-Count'));
    }

    /**
     * check for a client error if invalid limit value is provided
     *
     * @dataProvider invalidPagingPageSizeProvider
     *
     * @param integer $limit limit value that should fail
     * @return void
     */
    public function testInvalidPagingPageSize($limit)
    {
        $client = static::createRestClient();
        $client->request('GET', sprintf('/core/app/?limit(%s)', $limit));

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('syntax error in rql', $client->getResults()->message);
    }

    /**
     * page size test provides
     *
     * @return array[]
     */
    public function invalidPagingPageSizeProvider()
    {
        return [
            [0],
            [-1],
        ];
    }

    /**
     * RQL is parsed only when we get apps
     *
     * @return void
     */
    public function testRqlIsParsedOnlyOnGetRequest()
    {
        $appData = [
            'showInMenu' => false,
            'order'      => 100,
            'name'      => ['en' => 'Administration'],
        ];

        $client = static::createRestClient();
        $client->request('GET', '/core/app/?invalidrqlquery');
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('syntax error in rql', $client->getResults()->message);

        $client = static::createRestClient();
        $client->request('GET', '/core/app/admin?invalidrqlquery');
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('syntax error in rql', $client->getResults()->message);

        $client = static::createRestClient();
        $client->request('OPTIONS', '/core/app/?invalidrqlquery');
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        $client = static::createRestClient();
        $client->request('OPTIONS', '/schema/core/app/collection?invalidrqlquery');
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        $client = static::createRestClient();
        $client->request('OPTIONS', '/schema/core/app/item?invalidrqlquery');
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        $client = static::createRestClient();
        $client->request('GET', '/schema/core/app/collection?invalidrqlquery');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $client = static::createRestClient();
        $client->request('GET', '/schema/core/app/item?invalidrqlquery');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());


        $client = static::createRestClient();
        $client->post('/core/app/?invalidrqlquery', $appData);
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());

        $client = static::createRestClient();
        $client->put('/core/app/admin?invalidrqlquery', $appData);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        $client = static::createRestClient();
        $client->request('DELETE', '/core/app/admin?invalidrqlquery');
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
    }

    /**
     * Test only RQL select() operator is allowed for GET one
     *
     * @return void
     * @group tmp
     */
    public function testOnlyRqlSelectIsAllowedOnGetOne()
    {
        $client = static::createRestClient();
        $client->request('GET', '/core/app/?select(id)');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $client = static::createRestClient();
        $client->request('GET', '/core/app/admin?select(id)');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        foreach ([
                     'limit' => 'limit(1)',
                     'sort'  => 'sort(+id)',
                     'eq'    => 'eq(id,a)',
                 ] as $extraRqlOperator => $extraRqlOperatorQuery) {
            $client = static::createRestClient();
            $client->request('GET', '/core/app/?select(id)&'.$extraRqlOperatorQuery);
            $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

            $client = static::createRestClient();
            $client->request('GET', '/core/app/admin?select(id)&'.$extraRqlOperatorQuery);
            $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
            $this->assertEquals(
                sprintf('RQL operator "%s" is not allowed for this request', $extraRqlOperator),
                $client->getResults()->message
            );
        }
    }

    /**
     * check for empty collections when no fixtures are loaded
     *
     * @return void
     */
    public function testFindAllEmptyCollection()
    {
        // reset fixtures since we already have some from setUp
        $this->loadFixturesLocal([]);
        $client = static::createRestClient();
        $client->request('GET', '/core/app/');

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::CONTENT_TYPE, $response);
        $this->assertResponseSchemaRel(self::SCHEMA_URL_COLLECTION, $response);

        $this->assertEquals([], $results);
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
        $this->assertResponseSchemaRel(self::SCHEMA_URL_ITEM, $response);

        $this->assertEquals('admin', $results->id);
        $this->assertEquals('Administration', $results->name->en);
        $this->assertEquals(true, $results->showInMenu);

        $this->assertStringContainsString(
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
        $testApp->name = new \stdClass;
        $testApp->name->en = 'new Test App';
        $testApp->showInMenu = true;

        $client = static::createRestClient();
        $client->post('/core/app/', $testApp);
        $response = $client->getResponse();
        $results = $client->getResults();

        // we sent a location header so we don't want a body
        $this->assertNull($results);
        $this->assertStringContainsString('/core/app/', $response->headers->get('Location'));

        $client = static::createRestClient();
        $client->request('GET', $response->headers->get('Location'));
        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::CONTENT_TYPE, $response);
        $this->assertResponseSchemaRel(self::SCHEMA_URL_ITEM, $response);
        $this->assertEquals('new Test App', $results->name->en);
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
        $client->post('/core/app/', "", [], [], [], false);

        $response = $client->getResponse();

        $this->assertStringContainsString(
            'No input data',
            $response->getContent()
        );

        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * test if we get a correct return if we post empty.
     *
     * @return void
     */
    public function testPostNonObjectApp()
    {
        $client = static::createRestClient();
        $client->post('/core/app/', "non-object value");

        $response = $client->getResponse();
        $this->assertStringContainsString('JSON request body must be an object', $response->getContent());
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
        $testApp->name = new \stdClass;
        $testApp->name->en = 'new Test App';
        $testApp->showInMenu = true;

        // malform it ;-)
        $input = str_replace(":", ";", json_encode($testApp));

        $client = static::createRestClient();

        // make sure this is sent as 'raw' input (not json_encoded again)
        $client->post('/core/app/', $input, [], [], [], false);

        $response = $client->getResponse();

        // Check that error message contains detailed reason
        json_decode($input);
        $lastJsonError = function_exists('json_last_error_msg')
            ? json_last_error_msg()
            : 'Unable to decode JSON string';
        $this->assertStringContainsString(
            $lastJsonError,
            $client->getResults()->message
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
            'Bad Request - "id" can not be given on a POST request. '.
            'Do a PUT request instead to update an existing record.',
            $client->getResults()->message
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
        $helloApp->name = new \stdClass();
        $helloApp->name->en = "Tablet";
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
        $this->assertResponseSchemaRel(self::SCHEMA_URL_ITEM, $response);
        $this->assertEquals('Tablet', $results->name->en);
        $this->assertFalse($results->showInMenu);
        $this->assertContains(
            '<http://localhost/core/app/tablet>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );
    }

    /**
     * Test for PATCH Request
     *
     * @return void
     */
    public function testPatchAppRequestApplyChanges()
    {
        $helloApp = new \stdClass();
        $helloApp->id = "testapp";
        $helloApp->name = new \stdClass();
        $helloApp->name->en = "Test App";
        $helloApp->showInMenu = false;

        // 1. Create some App
        $client = static::createRestClient();
        $client->put('/core/app/' . $helloApp->id, $helloApp);

        // 2. PATCH request
        $client = static::createRestClient();
        $patchJson = json_encode(
            [
                [
                    'op' => 'replace',
                    'path' => '/name/en',
                    'value' => 'Test App Patched'
                ]
            ]
        );
        $client->request('PATCH', '/core/app/' . $helloApp->id, [], [], [], $patchJson);
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        // 3. Get changed App and check changed title
        $client = static::createRestClient();
        $client->request('GET', '/core/app/' . $helloApp->id);

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::CONTENT_TYPE, $response);
        $this->assertResponseSchemaRel(self::SCHEMA_URL_ITEM, $response);
        $this->assertEquals('Test App Patched', $results->name->en);
    }

    /**
     * Test for Malformed PATCH Request
     *
     * @return void
     */
    public function testMalformedPatchAppRequest()
    {
        $helloApp = new \stdClass();
        $helloApp->id = "testapp";
        $helloApp->title = new \stdClass();
        $helloApp->title->en = "Test App";
        $helloApp->showInMenu = false;

        // 1. Create some App
        $client = static::createRestClient();
        $client->put('/core/app/' . $helloApp->id, $helloApp);

        // 2. PATCH request
        $client = static::createRestClient();
        $patchJson = json_encode(
            array(
                'op' => 'unknown',
                'path' => '/title/en'
            )
        );
        $client->request('PATCH', '/core/app/' . $helloApp->id, [], [], [], $patchJson);
        $response = $client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString(
            'Invalid JSON patch request',
            $response->getContent()
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
        $helloApp->name = new \stdClass();
        $helloApp->name->en = "Tablet";
        $helloApp->showInMenu = false;

        $client = static::createRestClient();
        $client->put('/core/app/someotherapp', $helloApp);

        $response = $client->getResponse();

        $this->assertStringContainsString(
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
        $helloApp->name = new \stdClass();
        $helloApp->name->en = 'New tablet';
        $helloApp->showInMenu = false;

        $client = static::createRestClient();
        $client->put('/core/app/tablet', $helloApp);

        // we sent a location header so we don't want a body
        $this->assertNull($client->getResults());

        $client = static::createRestClient();
        $client->request('GET', '/core/app/tablet');
        $results = $client->getResults();

        $this->assertEquals('tablet', $results->id);
        $this->assertEquals('New tablet', $results->name->en);
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
        $isnogudApp->name = new \stdClass;
        $isnogudApp->name->en = 'I don\'t exist';
        $isnogudApp->showInMenu = true;

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
        $testApp->name = 'Tablet';
        $testApp->showInMenu = true;
        $testApp->order = 1;

        $client = static::createRestClient();
        $client->request('DELETE', '/core/app/tablet');

        $response = $client->getResponse();

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals('*', $response->headers->get('Access-Control-Allow-Origin'));
        $this->assertEmpty($response->getContent());

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
        $helloApp->name = new \stdClass;
        $helloApp->name->en = 'Tablet';
        $helloApp->showInMenu = 'false';

        $client = static::createRestClient();
        $client->put('/core/app/tablet', $helloApp);

        $results = $client->getResults();

        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        $this->assertStringContainsString('showInMenu', $results[0]->propertyPath);
        $this->assertEquals('String value found, but a boolean is required', $results[0]->message);
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

        $this->assertCorsHeaders('GET, POST, PUT, PATCH, DELETE, OPTIONS', $response);
    }

    /**
     * requests on OPTIONS and HEAD shall not lead graviton to get any data from mongodb.
     * if we page limit(1) this will lead to presence of the x-total-count header if
     * data is generated (asserted by testGetAppPagingWithRql()). thus, if we don't
     * have this header, we can safely assume that no data has been processed in RestController.
     *
     * @return void
     */
    public function testNoRecordsAreGeneratedOnPreRequests()
    {
        $client = static::createRestClient();
        $client->request('OPTIONS', '/core/app/?limit(1)');
        $response = $client->getResponse();
        $this->assertArrayNotHasKey('x-total-count', $response->headers->all());

        $client = static::createRestClient();
        $client->request('HEAD', '/core/app/?limit(1)');
        $response = $client->getResponse();
        $this->assertArrayNotHasKey('x-total-count', $response->headers->all());
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
        $this->assertEquals('en', $results->items->properties->name->required[0]);

        $this->assertCorsHeaders('GET, POST, PUT, PATCH, DELETE, OPTIONS', $response);
        $this->assertContains(
            'Link',
            explode(',', $response->headers->get('Access-Control-Expose-Headers'))
        );

        $this->assertStringContainsString(
            '<http://localhost/schema/core/app/collection>; rel="self"',
            $response->headers->get('Link')
        );
    }

    /**
     * Test for searchable translations
     *
     * @dataProvider searchableTranslationDataProvider
     *
     * @param string $expr     expression
     * @param int    $expCount count
     *
     * @return void
     */
    public function testSearchableTranslations($expr, $expCount)
    {
        $client = static::createRestClient();
        $client->request(
            'GET',
            '/core/app/?'.$expr,
            [],
            [],
            [
                'HTTP_ACCEPT_LANGUAGE' => 'en, de'
            ]
        );

        $result = $client->getResults();
        $this->assertCount($expCount, $result);

        // try the same via header!
        $client = static::createRestClient();
        $client->request(
            'GET',
            '/core/app/',
            [],
            [],
            [
                'HTTP_ACCEPT_LANGUAGE' => 'en, de',
                'HTTP_X-RQL-QUERY' => $expr
            ]
        );

        $result = $client->getResults();
        $this->assertCount($expCount, $result);
    }

    /**
     * see that if primary locale is not in accept languages, that we ca apply
     * changes on translatables anyway..
     *
     * @return void
     */
    public function testUpdateTranslatableOnlySecondaryLocale()
    {
        // 2. PATCH request
        $client = static::createRestClient();
        $patchJson = json_encode(
            [
                [
                    'op' => 'replace',
                    'path' => '/name/de',
                    'value' => 'Mein neuer Name'
                ]
            ]
        );
        $client->request('PATCH', '/core/app/tablet', [], [], ['HTTP_ACCEPT_LANGUAGE' => 'de'], $patchJson);
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $client = static::createRestClient();

        $client->request('GET', '/core/app/tablet', [], [], ['HTTP_ACCEPT_LANGUAGE' => 'de,en']);
        $result = $client->getResults();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Tablet', $result->name->en);
        $this->assertEquals('Mein neuer Name', $result->name->de);
    }

    /**
     * data provider for searchable translations
     *
     * @return array data
     */
    public function searchableTranslationDataProvider()
    {
        return [
            'simple-de' => array('eq(name.de,Die%20Administration)', 1),
            'non-existent' => array('eq(name.de,Administration)', 0),
            'english' => array('eq(name.en,Administration)', 1),
            'no-lang' => array('eq(name,Administration)', 1),
            'glob' => array('like(name.de,*Administr*)', 1),
            'all-glob' => array('like(name.de,*a*)', 2)
        ];
    }

    /**
     * ensure we have nice parse error output in rql parse failure
     *
     * @return void
     */
    public function testRqlSyntaxError()
    {
        $client = static::createRestClient();

        $client->request('GET', '/core/app/?eq(name)');

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertEquals(400, $response->getStatusCode());

        $this->assertStringContainsString('syntax error in rql: ', $results->message);
        $this->assertStringContainsString('Unexpected token', $results->message);
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
        $this->assertEquals('Unique identifier', $schema->properties->id->description);
        $this->assertContains('id', $schema->required);

        $this->assertEquals('object', $schema->properties->name->type);
        $this->assertEquals('translatable', $schema->properties->name->format);
        $this->assertEquals('Name', $schema->properties->name->title);
        $this->assertEquals('Display name for an app.', $schema->properties->name->description);
        $this->assertEquals('string', $schema->properties->name->properties->en->type);
        $this->assertContains('name', $schema->required);

        $this->assertEquals('boolean', $schema->properties->showInMenu->type);
        $this->assertEquals('Show in Menu', $schema->properties->showInMenu->title);
        $this->assertEquals(
            'Define if an app should be exposed on the top level menu.',
            $schema->properties->showInMenu->description
        );
    }
}
