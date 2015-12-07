<?php
/**
 * ProxyControllerTest
 */

namespace Graviton\ProxyBundle\Tests\Controller;

use Graviton\TestBundle\Client;
use Graviton\TestBundle\Test\RestTestCase;

/**
 * functional test for /3rdparty/{api}
 *
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link    http://swisscom.ch
 */
class ProxyControllerTest extends RestTestCase
{
    /**
     * @var string
     */
    const REQUEST_URL = "/3rdparty/graviton/core/app";

    /**
     * test post request with the proxy Action
     *
     * @return void
     */
    public function testProxyAction()
    {
        $client = static::createRestClient();
        $headers = array(
            'Content_Type'  => 'application/json',
        );

        $testApp = new \stdClass();
        $testApp->id = "testapp";
        $testApp->showInMenu = false;
        $testApp->order = 33;
        $testApp->name = new \stdClass();
        $testApp->name->en = "testapp";

        $client->request(
            'PUT',
            self::REQUEST_URL.'/'.$testApp->id,
            array(),
            array(),
            $headers,
            json_encode($testApp)
        );

        $response = $client->getResponse();

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());

        $client->request(
            'GET',
            self::REQUEST_URL.'/'.$testApp->id,
            array(),
            array(),
            $headers
        );
        $response = $client->getResponse();
        $content = json_decode($response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($testApp, $content);

        $client->request(
            'DELETE',
            self::REQUEST_URL.'/'.$testApp->id,
            array(),
            array(),
            $headers
        );
        $response = $client->getResponse();
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * test the schema proxy Action
     *
     * @return void
     */
    public function testSchemaProxyAction()
    {
        $client = static::createRestClient();
        $client->request(
            'GET',
            '/schema'.self::REQUEST_URL.'/item'
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
