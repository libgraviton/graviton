<?php
/**
 * functional test for /core/app
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\CoreBundle\Event\HomepageRenderEvent;
use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Basic functional test for /.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class MainControllerTest extends RestTestCase
{
    /**
     * @const vendorized app mime type for data
     */
    const CONTENT_TYPE = 'application/json; charset=UTF-8';
    /**
     * @const corresponding vendorized schema mime type
     */
    const SCHEMA_TYPE = 'application/json; charset=UTF-8';

    /**
     * RQL query is ignored
     *
     * @return void
     */
    public function testRqlIsIgnored()
    {
        $client = static::createRestClient();
        $client->request('GET', '/?invalidrqlquery');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    /**
     * check if version is returned in header
     *
     * @return void
     */
    public function testVersionHeader()
    {
        $client = static::createRestClient();
        $client->request('GET', '/');

        $response = $client->getResponse();
        $this->assertEquals(
            $this->getContainer()->getParameter('graviton.core.version.header'),
            $response->headers->get('X-Version')
        );
    }

    /**
     * check for response contents.
     *
     * @return void
     */
    public function testRequestBody()
    {
        $client = static::createRestClient();
        $client->request('GET', '/');

        $results = $client->getResults();

        $this->assertIsArray($results->services);

        $serviceRefs = array_map(
            function ($service) {
                return $service->{'$ref'};
            },
            $results->services
        );
        $this->assertContains('http://localhost/core/app/', $serviceRefs);

        $profiles = array_map(
            function ($service) {
                return $service->{'api-docs'}->json->{'$ref'};
            },
            $results->services
        );
        $this->assertContains('http://localhost/schema/core/app/openapi.json', $profiles);
    }
}
