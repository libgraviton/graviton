<?php
/**
 * functional test for /core/product
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;

/**
 * Basic functional test for /core/product.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ProductControllerTest extends RestTestCase
{
    /**
     * @const complete content type string expected on a resouce
     */
    const CONTENT_TYPE = 'application/json; charset=UTF-8; profile=http://localhost/schema/core/product/item';

    /**
     * @const corresponding vendorized schema mime type
     */
    const COLLECTION_TYPE = 'application/json; charset=UTF-8; profile=http://localhost/schema/core/product/collection';

    /**
     * setup client and load fixtures
     *
     * @return void
     */
    public function setUp()
    {
        $this->loadFixtures(
            array(
                'Graviton\CoreBundle\DataFixtures\MongoDB\LoadProductData',
                'Graviton\I18nBundle\DataFixtures\MongoDB\LoadLanguageData',
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
        $client->request('GET', '/core/product/');

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::COLLECTION_TYPE, $response);

        $expected = array(
            1 => 'Checking account',
            2 => 'Savings Account',
            3 => 'Money market account',
            4 => 'Mortgage',
            5 => 'Personal loan',
            6 => 'Mutual fund',
            7 => 'Revolving credit',
            8 => 'Business loan',
        );

        $this->assertEquals(count($expected), count($results));

        foreach ($expected as $key => $name) {
            $this->assertEquals($key, $results[$key - 1]->id);
            $this->assertEquals($name, $results[$key - 1]->name->en);
        }

        $this->assertContains(
            '<http://localhost/core/product/>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );
        $this->assertEquals('*', $response->headers->get('Access-Control-Allow-Origin'));
    }

    /**
     * check for caching on product resource
     *
     * @return void
     */
    public function testProductIsCached()
    {
        $client = static::createRestClient();
        $client->request('GET', '/core/product');

        $etag = $client->getResponse()->headers->get('ETag');

        $this->assertInternalType('string', $etag);

        // client has to be rebuild since the AppKernel will be resetted after a request
        // which will unregister bundles registered by bundle loader.
        $client = static::createRestClient();
        $client->request('GET', '/core/product', array(), array(), array('HTTP_If-None-Match' => $etag));

        $this->assertEmpty($client->getResponse()->getContent());
    }

    /**
     * test getting schema information
     *
     * @return void
     */
    public function testGetProductSchemaInformation()
    {
        $client = static::createRestClient();
        $client->request('OPTIONS', '/core/product/1');

        $response = $client->getResponse();

        $this->assertCorsHeaders('GET, OPTIONS', $response);

        $this->assertContains(
            '<http://localhost/core/product/1>; rel="self"',
            explode(',', $response->headers->get('Link'))
        );
    }

    /**
     * test getting schema information from canonical url
     *
     * @return void
     */
    public function testGetProductSchemaInformationCanonical()
    {
        $client = static::createRestClient();
        $client->request('GET', '/schema/core/product/item');

        $this->assertIsSchemaResponse($client->getResponse());
        $this->assertIsProductSchema($client->getResults());
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
     * check if a schema is of the product type
     *
     * @param \stdClass $schema schema from service to validate
     *
     * @return void
     */
    private function assertIsProductSchema(\stdClass $schema)
    {
        $this->assertEquals('Product', $schema->title);
        $this->assertEquals('A product.', $schema->description);
        $this->assertEquals('object', $schema->type);

        $this->assertEquals('string', $schema->properties->id->type);
        $this->assertEquals('ID', $schema->properties->id->title);
        $this->assertEquals('Unique identifier for a product.', $schema->properties->id->description);
        $this->assertContains('id', $schema->required);

        $this->assertEquals('object', $schema->properties->name->type);
        $this->assertEquals('Name', $schema->properties->name->title);
        $this->assertEquals('Display name for a product.', $schema->properties->name->description);
        $this->assertEquals('string', $schema->properties->name->properties->en->type);
        $this->assertContains('name', $schema->required);
    }
}
