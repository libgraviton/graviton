<?php
/**
 * integration tests schema cache invalidation
 */

namespace Graviton\SchemaBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SchemaCacheInvalidationTest extends RestTestCase
{
    /**
     * test if the invalidation of the schema cache works as expected
     *
     * @return void
     */
    public function testSchemaCacheInvalidation()
    {

        $client = static::createRestClient();
        $client->request('GET', '/schema/testcase/schema-cache-invalidation/item');

        // 'en' must exist
        $this->assertObjectHasProperty('en', $client->getResults()->properties->name->properties);

        // make sure they not exist before - no properties
        $this->assertObjectNotHasProperty('es', $client->getResults()->properties->name->properties);
        $this->assertObjectNotHasProperty('properties', $client->getResults()->properties->apps);

        // insert new Language
        $newLang = (object) [
            'id' => 'es',
            'name' => (object) [
                'en' => 'Spanish'
            ]
        ];
        $client = static::createRestClient();
        $client->put('/i18n/language/es', $newLang);

        // insert new App
        $newApp = (object) [
            'id' => 'testapp',
            'name' => (object) [
                'en' => 'TestApp'
            ],
            'showInMenu' => true
        ];
        $client = static::createRestClient();
        $client->put('/core/app/testapp', $newApp);

        $client = static::createRestClient();
        $client->request('GET', '/schema/testcase/schema-cache-invalidation/item');

        // now, 'es' should be in the translatable schema
        $this->assertObjectHasProperty('es', $client->getResults()->properties->name->properties);

        // and our testapp should be a property of the x-dynamic-key
        $this->assertObjectHasProperty('testapp', $client->getResults()->properties->apps->properties);
    }
}
