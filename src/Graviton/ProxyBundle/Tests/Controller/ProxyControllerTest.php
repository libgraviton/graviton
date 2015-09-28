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
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ProxyControllerTest extends RestTestCase
{
    /**
     * @var string
     */
    const REQUEST_URL = "/3rdparty/petstore/v2/user";

    /**
     * @var Client
     */
    private $client;

    /**
     * @var \stdClass
     */
    private $testUser;

    /**
     * @var array
     */
    private $headers;

    /**
     * @inheritDoc
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->client = static::createRestClient();
        $this->headers = array(
            'Content_Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => 'special_key',
        );
        $this->testUser = new \stdClass();
        $this->testUser->id = 123456;
        $this->testUser->username = "tester";
        $this->testUser->firstName = "test";
        $this->testUser->userStatus = 1;
    }


    /**
     * test post request with the proxy Action
     *
     * @return void
     */
    public function testPostProxyAction()
    {
        $this->client->request(
            'POST',
            self::REQUEST_URL,
            array(),
            array(),
            $this->headers,
            json_encode($this->testUser)
        );

        $response = $this->client->getResponse();
        $this->assertEquals('application/json', $response->headers->get("Content-Type"));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    /**
     * test the proxy Action
     *
     * @depends testPostProxyAction
     *
     * @return void
     */
    public function testProxyAction()
    {
        $this->client->request(
            'GET',
            self::REQUEST_URL.'/'.$this->testUser->username,
            array(),
            array(),
            $this->headers
        );
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($this->testUser, $content);
    }

    /**
     * test url encoding
     *
     * @depends testProxyAction
     *
     * @return void
     */
    public function testDeleteProxyAction()
    {
        $this->client->request(
            'DELETE',
            self::REQUEST_URL.'/'.$this->testUser->username,
            array(),
            array(),
            $this->headers
        );
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }
}
