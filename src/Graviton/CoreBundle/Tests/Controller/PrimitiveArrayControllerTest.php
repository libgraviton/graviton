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
            'strarray'  => ['a', 'b'],
            'boolarray' => [true, false],
            'hasharray' => [(object) ['x' => 'y'], (object) []],

            'hash'      => (object) [
                'intarray'  => [10, 20],
                'strarray'  => ['a', 'b'],
                'boolarray' => [true, false],
                'hasharray' => [(object) ['x' => 'y'], (object) []],
            ],

            'arrayhash' => [
                (object) [
                    'intarray'  => [10, 20],
                    'strarray'  => ['a', 'b'],
                    'boolarray' => [true, false],
                    'hasharray' => [(object) ['x' => 'y'], (object) []],
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
            'strarray'  => ['a', 'b'],
            'boolarray' => [true, false],
            'hasharray' => [(object) ['x' => 'y'], (object) []],

            'hash'      => (object) [
                'intarray'  => [10, 20],
                'strarray'  => ['a', 'b'],
                'boolarray' => [true, false],
                'hasharray' => [(object) ['x' => 'y'], (object) []],
            ],

            'arrayhash' => [
                (object) [
                    'intarray'  => [10, 20],
                    'strarray'  => ['a', 'b'],
                    'boolarray' => [true, false],
                    'hasharray' => [(object) ['x' => 'y'], (object) []],
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

            'intarray'  => [1, 'a'],
            'strarray'  => ['a', false],
            'boolarray' => [true, 'a'],
            'hasharray' => [(object) ['x' => 'y'], 1.5],

            'hash'      => (object) [
                'intarray'  => [1, 'a'],
                'strarray'  => ['a', false],
                'boolarray' => [true, 'a'],
                'hasharray' => [(object) ['x' => 'y'], 1.5],
            ],

            'arrayhash' => [
                (object) [
                    'intarray'  => [1, 'a'],
                    'strarray'  => ['a', false],
                    'boolarray' => [true, 'a'],
                    'hasharray' => [(object) ['x' => 'y'], 1.5],
                ]
            ],
        ];

        $client = static::createRestClient();
        $client->put('/testcase/primitivearray/testdata', $data);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertNotNull($client->getResults());

        // boolean and string values are always converted to correct type by symfony form.
        // we never get "This value is not valid" for such types
        $this->assertEquals(
            [
                (object) [
                    'propertyPath' => 'children[intarray].children[1]',
                    'message'      => 'This value is not valid.'
                ],
                (object) [
                    'propertyPath' => 'data.hasharray[1]',
                    'message'      => 'This value should be of type object.',
                ],

                (object) [
                    'propertyPath' => 'children[hash].children[intarray].children[1]',
                    'message'      => 'This value is not valid.',
                ],
                (object) [
                    'propertyPath' => 'data.hash.hasharray[1]',
                    'message'      => 'This value should be of type object.',
                ],

                (object) [
                    'propertyPath' => 'children[arrayhash].children[0].children[intarray].children[1]',
                    'message'      => 'This value is not valid.',
                ],
                (object) [
                    'propertyPath' => 'data.arrayhash[0].hasharray[1]',
                    'message'      => 'This value should be of type object.',
                ],
            ],
            $client->getResults()
        );
    }
}
