<?php
/**
 * SimpleExtrefControllerTest
 */

namespace Graviton\Tests\Rest\Controller;

use Graviton\Tests\RestTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SimpleExtrefControllerTest extends RestTestCase
{

    /**
     * valid post
     *
     * @return void
     */
    public function testSimpleHandling()
    {
        $data = [
            'requiredExtref' => [
                '$ref' => 'http://localhost/core/app/admin'
            ]
        ];

        $client = static::createRestClient();
        $client->post('/testcase/simpleextref/', $data);
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertEmpty($client->getResults());
    }

    /**
     * invalid post
     *
     * @return void
     */
    public function testSimpleHandlingNonExisting()
    {
        $data = [
            'requiredExtref' => [
                '$ref' => 'http://localhost/non-existing/service'
            ]
        ];

        $client = static::createRestClient();
        $client->post('/testcase/simpleextref/', $data);
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertEmpty($client->getResults());
    }
}
