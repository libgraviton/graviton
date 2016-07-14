<?php
/**
 * integration tests schema cache invalidation
 */

namespace Graviton\SchemaBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
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
        $this->assertObjectHasAttribute('en', $client->getResults()->properties->name->properties);

        // make sure they not exist before - no properties
        $this->assertObjectNotHasAttribute('es', $client->getResults()->properties->name->properties);
        $this->assertObjectNotHasAttribute('properties', $client->getResults()->properties->apps);

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
        $this->assertObjectHasAttribute('es', $client->getResults()->properties->name->properties);

        // and our testapp should be a property of the x-dynamic-key
        $this->assertObjectHasAttribute('testapp', $client->getResults()->properties->apps->properties);
    }
}
