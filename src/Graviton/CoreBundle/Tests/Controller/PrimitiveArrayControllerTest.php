<?php
/**
 * PrimitiveArrayControllerTest class file
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;
use GravitonDyn\TestCasePrimitiveArrayBundle\DataFixtures\MongoDB\LoadTestCasePrimitiveArrayData;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class PrimitiveArrayControllerTest extends RestTestCase
{
    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp()
    {
        if (!class_exists(LoadTestCasePrimitiveArrayData::class)) {
            $this->markTestSkipped('TestCasePrimitiveArray definition is not loaded');
        }

        $this->loadFixtures(
            [LoadTestCasePrimitiveArrayData::class],
            null,
            'doctrine_mongodb'
        );
    }

    /**
     * Test GET one method
     *
     * @return void
     */
    public function testCheckGetOne()
    {
        $client = static::createRestClient();
        $client->request('GET', '/testcase/primitivearray/testdata');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertNotEmpty($client->getResults());

        $data = $client->getResults();

        $this->assertInternalType('array', $data->intarray);
        foreach ($data->intarray as $value) {
            $this->assertInternalType('integer', $value);
        }

        $this->assertInternalType('array', $data->hash->intarray);
        foreach ($data->hash->intarray as $value) {
            $this->assertInternalType('integer', $value);
        }
    }

    /**
     * Test GET all method
     *
     * @return void
     */
    public function testCheckGetAll()
    {
        $client = static::createRestClient();
        $client->request('GET', '/testcase/primitivearray/');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $client->getResults());

        $data = $client->getResults()[0];

        $this->assertInternalType('array', $data->intarray);
        foreach ($data->intarray as $value) {
            $this->assertInternalType('integer', $value);
        }

        $this->assertInternalType('array', $data->hash->intarray);
        foreach ($data->hash->intarray as $value) {
            $this->assertInternalType('integer', $value);
        }
    }

    /**
     * Test POST method
     *
     * @return void
     */
    public function testPostMethod()
    {
        $data = (object) [
            'intarray'  => [10, 20],
            'hash'      => (object) [
                'intarray'  => [30, 40],
            ],
            'arrayhash' => [
                (object) [
                    'intarray' => [30, 40],
                ]
            ],
        ];

        $client = static::createRestClient();
        $client->post('/testcase/primitivearray/', $data);
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertEmpty($client->getResults());

        $location = $client->getResponse()->headers->get('Location');

        $client = static::createRestClient();
        $client->request('GET', $location);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $result = $client->getResults();
        $this->assertNotNull($result->id);
        unset($result->id);
        $this->assertEquals($data, $result);
    }

    /**
     * Test PUT method
     *
     * @return void
     */
    public function testPutMethod()
    {
        $data = (object) [
            'id'        => 'testdata',
            'intarray'  => [10, 20],
            'hash'      => (object) [
                'intarray'  => [30, 40],
            ],
            'arrayhash' => [
                (object) [
                    'intarray' => [30, 40],
                ]
            ],
        ];

        $client = static::createRestClient();
        $client->put('/testcase/primitivearray/testdata', $data);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
        $this->assertEmpty($client->getResults());

        $client = static::createRestClient();
        $client->request('GET', '/testcase/primitivearray/testdata');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals($data, $client->getResults());
    }


    /**
     * Test validation
     *
     * @return void
     */
    public function testValidation()
    {
        $data = (object) [
            'id'        => 'testdata',
            'intarray'  => [true, false],
            'hash'      => (object) [
                'intarray'  => ['a', 'b'],
            ],
            'arrayhash' => [
                (object) [
                    'intarray' => [1.5, 2.5],
                ]
            ],
        ];

        $client = static::createRestClient();
        $client->put('/testcase/primitivearray/testdata', $data);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertNotNull($client->getResults());
        $this->assertEquals(
            [
                (object) [
                    'propertyPath' => 'data.intarray[0]',
                    'message'      => 'This value should be of type int.'
                ],
                (object) [
                    'propertyPath' => 'data.intarray[1]',
                    'message'      => 'This value should be of type int.'
                ],
                (object) [
                    'propertyPath' => 'data.hash.intarray[0]',
                    'message'      => 'This value should be of type int.'
                ],
                (object) [
                    'propertyPath' => 'data.hash.intarray[1]',
                    'message'      => 'This value should be of type int.'
                ],
                (object) [
                    'propertyPath' => 'data.arrayhash[0].intarray[0]',
                    'message'      => 'This value should be of type int.'
                ],
                (object) [
                    'propertyPath' => 'data.arrayhash[0].intarray[1]',
                    'message'      => 'This value should be of type int.'
                ]
            ],
            $client->getResults()
        );
    }
}
