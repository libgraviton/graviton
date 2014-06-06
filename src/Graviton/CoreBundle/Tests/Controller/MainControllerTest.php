<?php
/**
 * functional test for /core/app
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;

/**
 * Basic functional test for /.
 *
 * @category GravitonCoreBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class MainControllerTest extends RestTestCase
{
    /**
     * @const vendorized app mime type for data
     */
    const CONTENT_TYPE = 'application/vnd.graviton.core.main+json; charset=UTF-8';
    /**
     * @const corresponding vendorized schema mime type
     */
    const SCHEMA_TYPE = 'application/vnd.graviton.schema.core.main+json';

    /**
     * check if version is returned in header
     *
     * @return void
     */
    public function testVersionHeader()
    {
        $client = static::createRestClient();
        $client->request('GET', '/');

        $version = json_decode(file_get_contents(__DIR__.'/../../../../../composer.json'), true);
        $version = $version['version'];

        $response = $client->getResponse();

        $this->assertEquals($version, $response->headers->get('X-Version'));
    }

    /**
     * check for app link in header
     *
     * @return void
     */
    public function testAppsLink()
    {
        $client = static::createRestClient();
        $client->request('GET', '/');

        $response = $client->getResponse();

        $this->assertContains(
            '<http://localhost/core/app>; rel="apps"; type="application/vnd.graviton.schema.collection.app+json"',
            explode(',', $response->headers->get('Link'))
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

        $this->assertEquals(
            'Please look at the Link headers of this response for further information.',
            $results->message
        );
    }
}
