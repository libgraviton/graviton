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
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
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
            '<http://localhost/core/app>; rel="apps"; type="application/json"',
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

        $this->assertInternalType('array', $results->services);

        $refName = '$ref';
        $serviceRefs = array_map(
            function ($service) use ($refName) {
                return $service->$refName;
            },
            $results->services
        );
        $this->assertContains('http://localhost/core/app', $serviceRefs);

        $profiles = array_map(
            function ($service) {
                return $service->profile;
            },
            $results->services
        );
        $this->assertContains('http://localhost/schema/core/app/collection', $profiles);
    }
}
