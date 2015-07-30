<?php
/**
 * functional test for /core/module
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Basic functional test for /core/module.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ModuleControllerTest extends RestTestCase
{
    /**
     * @const complete content type string expected on a resouce
     */
    const CONTENT_TYPE = 'application/json; charset=UTF-8; profile=http://localhost/schema/core/module/item';

    /**
     * @const corresponding vendorized schema mime type
     */
    const COLLECTION_TYPE = 'application/json; charset=UTF-8; profile=http://localhost/schema/core/module/collection';

    /**
     * setup client and load fixtures
     *
     * @return void
     */
    public function setUp()
    {
        $this->loadFixtures(
            array(
                'Graviton\I18nBundle\DataFixtures\MongoDB\LoadLanguageData',
                'GravitonDyn\ModuleBundle\DataFixtures\MongoDB\LoadModuleData'
            ),
            null,
            'doctrine_mongodb'
        );
    }

    /**
     * test if we can get list of modules paged
     *
     * @return void
     */
    public function testGetModuleWithPaging()
    {
        $client = static::createRestClient();
        $client->request('GET', '/core/module?perPage=1');
        $response = $client->getResponse();

        $this->assertEquals(1, count($client->getResults()));

        $this->assertContains(
            '<http://localhost/core/module?page=1&perPage=1>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );

        $this->assertContains(
            '<http://localhost/core/module?page=2&perPage=1>; rel="next"',
            explode(',', $response->headers->get('Link'))
        );

        $this->assertContains(
            '<http://localhost/core/module?page=5&perPage=1>; rel="last"',
            explode(',', $response->headers->get('Link'))
        );

        $this->assertEquals('http://localhost/core/app/tablet', $client->getResults()[0]->app->{'$ref'});
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
        $client->request('GET', '/core/module');

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::COLLECTION_TYPE, $response);

        $this->assertEquals(array(), $results);
    }

    /**
     * test if we can get an module first by key and then its id (id is dynamic)
     *
     * @return void
     */
    public function testGetModuleWithKeyAndUseId()
    {
        $client = static::createRestClient();
        $client->request('GET', '/core/module?q='.urlencode('eq(key,investment)'));
        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::COLLECTION_TYPE, $response);
        $this->assertEquals('investment', $results[0]->key);
        $this->assertEquals(1, count($results));

        // get entry by id
        $moduleId = $results[0]->id;

        $client = static::createRestClient();
        $client->request('GET', '/core/module/'.$moduleId);
        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::CONTENT_TYPE, $response);
        $this->assertEquals($moduleId, $results->id);
        $this->assertEquals('investment', $results->key);
        $this->assertEquals('/module/investment', $results->path);
        $this->assertEquals(2, $results->order);

        $this->assertContains(
            '<http://localhost/core/module/'.$moduleId.'>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );
        $this->assertEquals('*', $response->headers->get('Access-Control-Allow-Origin'));
    }

    /**
     * test if we can create a module through POST
     *
     * @return void
     */
    public function testPostModule()
    {
        $testModule = new \stdClass;
        $testModule->key = 'test';
        $testModule->app = new \stdClass;
        $testModule->app->{'$ref'} = 'http://localhost/core/app/testapp';
        $testModule->name = new \stdClass;
        $testModule->name->en = 'Name';
        $testModule->path = '/test/test';
        $testModule->order = 50;

        $client = static::createRestClient();
        $client->post('/core/module', $testModule);
        $response = $client->getResponse();

        $client = static::createRestClient();
        $client->request('GET', $response->headers->get('Location'));
        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::CONTENT_TYPE, $response);

        $this->assertEquals('http://localhost/core/app/testapp', $results->app->{'$ref'});
        $this->assertEquals(50, $results->order);

        $this->assertContains(
            '<http://localhost/core/module/'.$results->id.'>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );
    }

    /**
     * test validation error for int types
     *
     * @return void
     */
    public function testPostInvalidInteger()
    {
        $testModule = new \stdClass;
        $testModule->key = 'test';
        $testModule->app = new \stdClass;
        $testModule->app->{'$ref'} = 'http://localhost/core/app/testapp';
        $testModule->name = new \stdClass;
        $testModule->name->en = 'Name';
        $testModule->path = '/test/test';
        $testModule->order = 'clearly a string';

        $client = static::createRestClient();
        $client->post('/core/module', $testModule);

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertEquals(400, $response->getStatusCode());

        $this->assertContains('order', $results[0]->propertyPath);
        $this->assertEquals('This value is not valid.', $results[0]->message);
    }

    /**
     * test updating module
     *
     * @return void
     */
    public function testPutModule()
    {
        // get id first..
        $client = static::createRestClient();
        $client->request('GET', '/core/module?q='.urlencode('eq(key,investment)'));
        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::COLLECTION_TYPE, $response);
        $this->assertEquals('investment', $results[0]->key);
        $this->assertEquals(1, count($results));

        // get entry by id
        $moduleId = $results[0]->id;

        $putModule = new \stdClass();
        $putModule->id = $moduleId;
        $putModule->key = 'test';
        $putModule->app = new \stdClass;
        $putModule->app->{'$ref'} = 'http://localhost/core/app/test';
        $putModule->name = new \stdClass;
        $putModule->name->en = 'testerle';
        $putModule->path = '/test/test';
        $putModule->order = 500;

        $client = static::createRestClient();
        $client->put('/core/module/'.$moduleId, $putModule);

        $client = static::createRestClient();
        $client->request('GET', '/core/module/'.$moduleId);
        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::CONTENT_TYPE, $response);

        $this->assertEquals($moduleId, $results->id);
        $this->assertEquals('http://localhost/core/app/test', $results->app->{'$ref'});
        $this->assertEquals(500, $results->order);

        $this->assertContains(
            '<http://localhost/core/module/'.$moduleId.'>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );
        $this->assertEquals('*', $response->headers->get('Access-Control-Allow-Origin'));
    }

    /**
     * test deleting a module
     *
     * @return void
     */
    public function testDeleteModule()
    {
        // get id first..
        $client = static::createRestClient();
        $client->request('GET', '/core/module?q='.urlencode('eq(key,investment)'));
        $results = $client->getResults();

        // get entry by id
        $moduleId = $results[0]->id;

        $client = static::createRestClient();
        $client->request('DELETE', '/core/module/'.$moduleId);

        $response = $client->getResponse();

        $this->assertResponseContentType(self::CONTENT_TYPE, $response);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('*', $response->headers->get('Access-Control-Allow-Origin'));

        $client->request('GET', '/core/module/'.$moduleId);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    /**
     * Test extref transformation
     *
     * @return void
     */
    public function testExtRefTransformation()
    {
        $client = static::createRestClient();

        $client->request('GET', '/core/module?q='.urlencode('eq(key,investment)'));
        $results = $client->getResults();
        $this->assertCount(1, $results);

        $module = $results[0];
        $this->assertEquals('investment', $module->key);
        $this->assertEquals('http://localhost/core/app/tablet', $module->app->{'$ref'});
        $this->assertEquals('http://localhost/core/product/3', $module->service[0]->gui->{'$ref'});
        $this->assertEquals('http://localhost/core/product/4', $module->service[0]->service->{'$ref'});
        $this->assertEquals('http://localhost/core/product/5', $module->service[1]->gui->{'$ref'});
        $this->assertEquals('http://localhost/core/product/6', $module->service[1]->service->{'$ref'});


        $module->app->{'$ref'} = 'http://localhost/core/app/admin';
        $module->service[0]->gui->{'$ref'} = 'http://localhost/core/app/admin';
        $module->service[0]->service->{'$ref'} = 'http://localhost/core/app/admin';
        $module->service[1]->gui->{'$ref'} = 'http://localhost/core/app/admin';
        $module->service[1]->service->{'$ref'} = 'http://localhost/core/app/admin';

        $client = static::createRestClient();
        $client->put('/core/module/'.$module->id, $module);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());


        $client = static::createRestClient();
        $client->request('GET', '/core/module/'.$module->id);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $module = $client->getResults();
        $this->assertEquals('http://localhost/core/app/admin', $module->app->{'$ref'});
        $this->assertEquals('http://localhost/core/app/admin', $module->service[0]->gui->{'$ref'});
        $this->assertEquals('http://localhost/core/app/admin', $module->service[0]->service->{'$ref'});
        $this->assertEquals('http://localhost/core/app/admin', $module->service[1]->gui->{'$ref'});
        $this->assertEquals('http://localhost/core/app/admin', $module->service[1]->service->{'$ref'});
    }

    /**
     * test getting collection schema.
     * i avoid retesting everything (covered in /core/app), this test only
     * asserts translatable & extref representation
     *
     * @return void
     */
    public function testGetModuleCollectionSchemaInformationFormat()
    {
        $client = static::createRestClient();

        $client->request('GET', '/schema/core/module/collection');
        $results = $client->getResults();

        $this->assertEquals('object', $results->items->properties->app->type);
        $this->assertEquals('string', $results->items->properties->app->properties->{'$ref'}->type);
        $this->assertEquals('extref', $results->items->properties->app->properties->{'$ref'}->format);

        $service = $results->items->properties->service;
        $this->assertEquals('array', $service->type);
        $this->assertEquals('object', $service->items->properties->name->type);
        $this->assertEquals('string', $service->items->properties->name->properties->en->type);
        $this->assertEquals('object', $service->items->properties->description->type);
        $this->assertEquals('string', $service->items->properties->description->properties->en->type);
        $this->assertEquals('object', $service->items->properties->gui->type);
        $this->assertEquals('object', $service->items->properties->service->type);
        $this->assertEquals('string', $service->items->properties->gui->properties->{'$ref'}->type);
        $this->assertEquals('extref', $service->items->properties->gui->properties->{'$ref'}->format);
        $this->assertEquals('string', $service->items->properties->service->properties->{'$ref'}->type);
        $this->assertEquals('extref', $service->items->properties->service->properties->{'$ref'}->format);
    }
}
