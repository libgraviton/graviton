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
        $client->request('GET', '/core/module/?limit(1)');
        $response = $client->getResponse();

        $this->assertEquals(1, count($client->getResults()));

        $this->assertContains(
            '<http://localhost/core/module/?limit(1)>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );

        $this->assertContains(
            '<http://localhost/core/module/?limit(1%2C1)>; rel="next"',
            $response->headers->get('Link')
        );

        $this->assertContains(
            '<http://localhost/core/module/?limit(1%2C5)>; rel="last"',
            $response->headers->get('Link')
        );

        $this->assertEquals('http://localhost/core/app/admin', $client->getResults()[0]->app->{'$ref'});
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
        $client->request('GET', '/core/module/');

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
        $client->request('GET', '/core/module/?eq(key,investment)');
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
     * test finding of modules by ref
     *
     * @dataProvider findByAppRefProvider
     *
     * @param string  $ref   which reference to search in
     * @param mixed   $url   ref to search for
     * @param integer $count number of results to expect
     *
     * @return void
     */
    public function testFindByAppRef($ref, $url, $count)
    {
        $this->loadFixtures(
            [
                'Graviton\I18nBundle\DataFixtures\MongoDB\LoadLanguageData',
                'GravitonDyn\ModuleBundle\DataFixtures\MongoDB\LoadModuleData',
                'Graviton\CoreBundle\DataFixtures\MongoDB\LoadAppData',
                'Graviton\CoreBundle\DataFixtures\MongoDB\LoadProductData',
            ],
            null,
            'doctrine_mongodb'
        );

        $url = sprintf(
            '/core/module/?%s=%s',
            $this->encodeRqlString($ref),
            $this->encodeRqlString($url)
        );

        $client = static::createRestClient();
        $client->request('GET', $url);
        $results = $client->getResults();
        $this->assertCount($count, $results);
    }

    /**
     * @return array
     */
    public function findByAppRefProvider()
    {
        return [
            'find all tablet records' => [
                'app.$ref',
                'http://localhost/core/app/tablet',
                5
            ],
            'find a linked record when searching for ref' => [
                'app.$ref',
                'http://localhost/core/app/admin',
                1
            ],
            'find nothing when searching for inextistant (and unlinked) ref' => [
                'app.$ref',
                'http://localhost/core/app/inexistant',
                0
            ],
            'return nothing when searching with incomplete ref' => [
                'app.$ref',
                'http://localhost/core/app',
                0
            ],
        ];
    }

    /**
     * Apply RQL operators to extref fields
     *
     * @dataProvider dataExtrefOperators
     *
     * @param string $rqlQuery    RQL query
     * @param array  $expectedIds Expected found IDs
     *
     * @return void
     */
    public function testExtrefOperators($rqlQuery, array $expectedIds)
    {
        $this->loadFixtures(
            [
                'Graviton\I18nBundle\DataFixtures\MongoDB\LoadLanguageData',
                'GravitonDyn\ModuleBundle\DataFixtures\MongoDB\LoadModuleData',
                'Graviton\CoreBundle\DataFixtures\MongoDB\LoadAppData',
                'Graviton\CoreBundle\DataFixtures\MongoDB\LoadProductData',
            ],
            null,
            'doctrine_mongodb'
        );

        $client = static::createRestClient();
        $client->request('GET', '/core/module/?'.$rqlQuery);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $foundIds = array_map(
            function ($item) {
                return $item->id;
            },
            $client->getResults()
        );

        sort($foundIds);
        sort($expectedIds);
        $this->assertEquals($expectedIds, $foundIds);
    }

    /**
     * @return array
     */
    public function dataExtrefOperators()
    {
        $tabletIds = [
            'tablet-realEstate',
            'tablet-investment',
            'tablet-retirement',
            'tablet-requisition',
            'tablet-payAndSave',
        ];
        $adminIds = [
            'admin-AdminRef',
        ];

        return [
            '== tablet' => [
                sprintf(
                    '%s=%s',
                    $this->encodeRqlString('app.$ref'),
                    $this->encodeRqlString('http://localhost/core/app/tablet')
                ),
                $tabletIds,
            ],
            '!= tablet' => [
                sprintf(
                    '%s!=%s',
                    $this->encodeRqlString('app.$ref'),
                    $this->encodeRqlString('http://localhost/core/app/tablet')
                ),
                $adminIds,
            ],
            '> tablet' => [
                sprintf(
                    '%s>%s',
                    $this->encodeRqlString('app.$ref'),
                    $this->encodeRqlString('http://localhost/core/app/tablet')
                ),
                [],
            ],
            '< tablet' => [
                sprintf(
                    '%s<%s',
                    $this->encodeRqlString('app.$ref'),
                    $this->encodeRqlString('http://localhost/core/app/tablet')
                ),
                $adminIds,
            ],
            '>= tablet' => [
                sprintf(
                    '%s>=%s',
                    $this->encodeRqlString('app.$ref'),
                    $this->encodeRqlString('http://localhost/core/app/tablet')
                ),
                $tabletIds,
            ],
            '<= tablet' => [
                sprintf(
                    '%s<=%s',
                    $this->encodeRqlString('app.$ref'),
                    $this->encodeRqlString('http://localhost/core/app/tablet')
                ),
                array_merge($tabletIds, $adminIds),
            ],
            '=in= tablet' => [
                sprintf(
                    '%s=in=(%s)',
                    $this->encodeRqlString('app.$ref'),
                    $this->encodeRqlString('http://localhost/core/app/tablet')
                ),
                $tabletIds,
            ],
            '=out= tablet' => [
                sprintf(
                    '%s=out=(%s)',
                    $this->encodeRqlString('app.$ref'),
                    $this->encodeRqlString('http://localhost/core/app/tablet')
                ),
                $adminIds,
            ],

            '> admin' => [
                sprintf(
                    '%s>%s',
                    $this->encodeRqlString('app.$ref'),
                    $this->encodeRqlString('http://localhost/core/app/admin')
                ),
                $tabletIds,
            ],
            '< admin' => [
                sprintf(
                    '%s<%s',
                    $this->encodeRqlString('app.$ref'),
                    $this->encodeRqlString('http://localhost/core/app/admin')
                ),
                [],
            ],
            '>= admin' => [
                sprintf(
                    '%s>=%s',
                    $this->encodeRqlString('app.$ref'),
                    $this->encodeRqlString('http://localhost/core/app/admin')
                ),
                array_merge($tabletIds, $adminIds),
            ],
            '<= admin' => [
                sprintf(
                    '%s<=%s',
                    $this->encodeRqlString('app.$ref'),
                    $this->encodeRqlString('http://localhost/core/app/admin')
                ),
                $adminIds,
            ],
            '=in= admin' => [
                sprintf(
                    '%s=in=(%s)',
                    $this->encodeRqlString('app.$ref'),
                    $this->encodeRqlString('http://localhost/core/app/admin')
                ),
                $adminIds,
            ],
            '=out= admin' => [
                sprintf(
                    '%s=out=(%s)',
                    $this->encodeRqlString('app.$ref'),
                    $this->encodeRqlString('http://localhost/core/app/admin')
                ),
                $tabletIds,
            ],

            '=in= admin, tablet' => [
                sprintf(
                    '%s=in=(%s,%s)',
                    $this->encodeRqlString('app.$ref'),
                    $this->encodeRqlString('http://localhost/core/app/admin'),
                    $this->encodeRqlString('http://localhost/core/app/tablet')
                ),
                array_merge($adminIds, $tabletIds),
            ],
            '=out= admin, tablet' => [
                sprintf(
                    '%s=out=(%s,%s)',
                    $this->encodeRqlString('app.$ref'),
                    $this->encodeRqlString('http://localhost/core/app/admin'),
                    $this->encodeRqlString('http://localhost/core/app/tablet')
                ),
                [],
            ],

            '== admin || == tablet' => [
                sprintf(
                    '(%s==%s|%s==%s)',
                    $this->encodeRqlString('app.$ref'),
                    $this->encodeRqlString('http://localhost/core/app/admin'),
                    $this->encodeRqlString('app.$ref'),
                    $this->encodeRqlString('http://localhost/core/app/tablet')
                ),
                array_merge($adminIds, $tabletIds),
            ],
            '== admin && == tablet' => [
                sprintf(
                    '(%s==%s&%s==%s)',
                    $this->encodeRqlString('app.$ref'),
                    $this->encodeRqlString('http://localhost/core/app/admin'),
                    $this->encodeRqlString('app.$ref'),
                    $this->encodeRqlString('http://localhost/core/app/tablet')
                ),
                [],
            ],

            '== admin || some logic' => [
                sprintf(
                    'or(eq(%s,%s),and(eq(id,%s),eq(id,%s)))',
                    $this->encodeRqlString('app.$ref'),
                    $this->encodeRqlString('http://localhost/core/app/admin'),
                    $this->encodeRqlString('not-existing-id-1'),
                    $this->encodeRqlString('not-existing-id-2')
                ),
                $adminIds,
            ],
            '== tablet && some logic' => [
                sprintf(
                    'and(eq(%s,%s),or(eq(id,%s),eq(id,%s)))',
                    $this->encodeRqlString('app.$ref'),
                    $this->encodeRqlString('http://localhost/core/app/tablet'),
                    $this->encodeRqlString($tabletIds[0]),
                    $this->encodeRqlString($tabletIds[1])
                ),
                [$tabletIds[0], $tabletIds[1]]
            ],
        ];
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
        $client->post('/core/module/', $testModule);
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

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
        $client->request('GET', '/core/module/?eq(key,investment)');
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
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

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
        $client->request('GET', '/core/module/?eq(key,investment)');
        $results = $client->getResults();

        // get entry by id
        $moduleId = $results[0]->id;

        $client = static::createRestClient();
        $client->request('DELETE', '/core/module/'.$moduleId);

        $response = $client->getResponse();

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals('*', $response->headers->get('Access-Control-Allow-Origin'));
        $this->assertEmpty($response->getContent());

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

        $client->request('GET', '/core/module/?eq(key,investment)');
        $results = $client->getResults();
        $this->assertCount(1, $results);

        $module = $results[0];
        $this->assertEquals('investment', $module->key);
        $this->assertEquals('http://localhost/core/app/tablet', $module->app->{'$ref'});

        $module->app->{'$ref'} = 'http://localhost/core/app/admin';

        $client = static::createRestClient();
        $client->put('/core/module/'.$module->id, $module);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());


        $client = static::createRestClient();
        $client->request('GET', '/core/module/'.$module->id);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $module = $client->getResults();
        $this->assertEquals('http://localhost/core/app/admin', $module->app->{'$ref'});
    }

    /**
     * Test extref validation
     *
     * @return void
     */
    public function testExtReferenceValidation()
    {
        $client = static::createRestClient();
        $client->request('GET', '/core/module/?eq(key,investment)');
        $this->assertCount(1, $client->getResults());

        $module = $client->getResults()[0];

        $urls = [
            'http://localhost',
            'http://localhost/core',
            'http://localhost/core/app',
            'http://localhost/core/noapp/admin',
        ];
        foreach ($urls as $url) {
            $module->app->{'$ref'} = $url;

            $client = static::createRestClient();
            $client->put('/core/module/'.$module->id, $module);
            $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
            $this->assertEquals(
                [
                    (object) [
                        'propertyPath' => 'children[app].children[ref]',
                        'message' => sprintf('URL "%s" is not a valid ext reference.', $url),
                    ],
                ],
                $client->getResults()
            );
        }
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
        $this->assertEquals('object', $service->items->properties->service->type);
        $this->assertEquals('string', $service->items->properties->service->properties->{'$ref'}->type);
    }

    /**
     * Encode RQL string
     *
     * @param string $value Value
     * @return string
     */
    private function encodeRqlString($value)
    {
        return strtr(
            rawurlencode($value),
            [
                '-' => '%2D',
                '_' => '%5F',
                '.' => '%2E',
                '~' => '%7E',
            ]
        );
    }
}
