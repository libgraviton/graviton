<?php
/**
 * functional test for /core/module
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;

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

        $this->assertEquals('http://localhost/core/app/tablet', $client->getResults()[0]->app);

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
        $testModule->app = 'http://localhost/core/app/testapp';
        $testModule->name = new \stdClass;
        $testModule->name->en = 'Name';
        $testModule->path = '/test/test';
        $testModule->order = 50;

        $client = static::createRestClient();
        $client->post('/core/module', $testModule);

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::CONTENT_TYPE, $response);

        $this->assertEquals('http://localhost/core/app/testapp', $results->app);
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
        $testModule->app = 'http://localhost/core/app/testapp';
        $testModule->name = new \stdClass;
        $testModule->name->en = 'Name';
        $testModule->path = '/test/test';
        $testModule->order = false;

        $client = static::createRestClient();
        $client->post('/core/module', $testModule);

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertEquals('order', $results[0]->property_path);
        $this->assertEquals('This value should be of type integer.', $results[0]->message);

        $this->assertEquals(400, $response->getStatusCode());
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
        $putModule->app = 'http://localhost/core/app/test';
        $putModule->name = new \stdClass();
        $putModule->name->en = 'testerle';
        $putModule->path = '/test/test';
        $putModule->order = 500;

        $client = static::createRestClient();
        $client->put('/core/module/'.$moduleId, $putModule);

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::CONTENT_TYPE, $response);

        $this->assertEquals($moduleId, $results->id);
        $this->assertEquals('http://localhost/core/app/test', $results->app);
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
}
