<?php
/**
 * NullExtrefControllerTest class file
 */

namespace Graviton\Tests\Rest\Controller;

use Graviton\Tests\RestTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class NullExtrefControllerTest extends RestTestCase
{
    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp() : void
    {
        if (!class_exists('GravitonDyn\TestCaseNullExtrefBundle\DataFixtures\MongoDB\LoadTestCaseNullExtrefData')) {
            $this->markTestSkipped('TestCaseNullExtref definition is not loaded');
        }

        $this->loadFixturesLocal(
            ['GravitonDyn\TestCaseNullExtrefBundle\DataFixtures\MongoDB\LoadTestCaseNullExtrefData']
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
        $client->request('GET', '/testcase/nullextref/testdata');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertNotEmpty($client->getResults());

        $data = $client->getResults();
        $expectedRefVal = 'http://localhost/core/app/admin';

        $this->assertFalse(isset($data->optionalExtref->{'$ref'}));
        $this->assertTrue(isset($data->requiredExtref->{'$ref'}));
        $this->assertEquals($expectedRefVal, $data->requiredExtref->{'$ref'});

        $this->assertFalse(isset($data->optionalExtrefArray[0]->{'$ref'}));
        $this->assertTrue(isset($data->requiredExtrefArray[0]->{'$ref'}));
        $this->assertEquals($expectedRefVal, $data->requiredExtrefArray[0]->{'$ref'});

        $this->assertFalse(isset($data->optionalExtrefDeep[0]->deep[0]->deep->deep[0]->{'$ref'}));
        $this->assertTrue(isset($data->requiredExtrefDeep[0]->deep[0]->deep->deep[0]->{'$ref'}));
        $this->assertEquals($expectedRefVal, $data->requiredExtrefDeep[0]->deep[0]->deep->deep[0]->{'$ref'});
    }

    /**
     * Test GET all method
     *
     * @return void
     */
    public function testCheckGetAll()
    {
        $client = static::createRestClient();
        $client->request('GET', '/testcase/nullextref/');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $client->getResults());

        $data = $client->getResults()[0];
        $expectedRefVal = 'http://localhost/core/app/admin';

        $this->assertFalse(isset($data->optionalExtref->{'$ref'}));
        $this->assertTrue(isset($data->requiredExtref->{'$ref'}));
        $this->assertEquals($expectedRefVal, $data->requiredExtref->{'$ref'});

        $this->assertFalse(isset($data->optionalExtrefArray[0]->{'$ref'}));
        $this->assertTrue(isset($data->requiredExtrefArray[0]->{'$ref'}));
        $this->assertEquals($expectedRefVal, $data->requiredExtrefArray[0]->{'$ref'});

        $this->assertFalse(isset($data->optionalExtrefDeep[0]->deep[0]->deep->deep[0]->{'$ref'}));
        $this->assertTrue(isset($data->requiredExtrefDeep[0]->deep[0]->deep->deep[0]->{'$ref'}));
        $this->assertEquals($expectedRefVal, $data->requiredExtrefDeep[0]->deep[0]->deep->deep[0]->{'$ref'});
    }

    /**
     * Test POST method
     *
     * @param array $data        Data to POST
     * @param array $compareData optional data to compare return to
     *
     * @return void
     * @dataProvider dataTestData
     */
    public function testPostMethod(array $data, array $compareData = null)
    {
        $client = static::createRestClient();
        $client->post('/testcase/nullextref/', $data);
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertEmpty($client->getResults());

        $location = $client->getResponse()->headers->get('Location');

        $client = static::createRestClient();
        $client->request('GET', $location);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $result = $client->getResults();
        $this->assertNotNull($result->id);
        unset($result->id);

        if (is_null($compareData)) {
            $compareData = $data;
        }

        $this->assertJsonStringEqualsJsonString(
            json_encode($this->removeNullRefs($compareData)),
            json_encode($result)
        );
    }

    /**
     * Test PUT method
     *
     * @param array $data        Data to PUT
     * @param array $compareData optional data to compare return to
     *
     * @return void
     * @dataProvider dataTestData
     */
    public function testPutMethod(array $data, array $compareData = null)
    {
        $data['id'] = 'testdata';

        $client = static::createRestClient();
        $client->put('/testcase/nullextref/testdata', $data);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
        $this->assertEmpty($client->getResults());

        $client = static::createRestClient();
        $client->request('GET', '/testcase/nullextref/testdata');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        if (is_null($compareData)) {
            $compareData = $data;
        } else {
            $compareData['id'] = $data['id'];
        }

        $this->assertJsonStringEqualsJsonString(
            json_encode($this->removeNullRefs($compareData)),
            json_encode($client->getResults())
        );
    }

    /**
     * Remove null $ref recursively
     *
     * @param array $data Data to process
     * @return array|object
     */
    private function removeNullRefs(array $data)
    {
        foreach ($data as $key => $value) {
            if ($key === '$ref' && $value === null) {
                unset($data[$key]);
                if ($data === []) {
                    return (object) $data;
                }
            } elseif (is_array($value)) {
                $data[$key] = $this->removeNullRefs($value);
            }
        }
        return $data;
    }

    /**
     * Data for tests
     *
     * @return array
     */
    public static function dataTestData(): array
    {
        return [
            'empty refs' => [
                [
                    'optionalExtref'      => ['$ref' => null],
                    'requiredExtref'      => ['$ref' => 'http://localhost/core/app/admin'],
                    'optionalExtrefArray' => [
                        ['$ref' => null],
                    ],
                    'requiredExtrefArray' => [
                        ['$ref' => 'http://localhost/core/app/admin'],
                    ],
                    'optionalExtrefDeep'  => [
                        [
                            'deep' => [
                                [
                                    'deep' => [
                                        'deep' => [
                                            ['$ref' => null],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'requiredExtrefDeep'  => [
                        [
                            'deep' => [
                                [
                                    'deep' => [
                                        'deep' => [
                                            ['$ref' => 'http://localhost/core/app/admin'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'requiredExtref'      => ['$ref' => 'http://localhost/core/app/admin'],
                    'optionalExtrefArray' => [],
                    'requiredExtrefArray' => [
                        ['$ref' => 'http://localhost/core/app/admin'],
                    ],
                    'optionalExtrefDeep'  => [
                        [
                            'deep' => [
                                [
                                    'deep' => [
                                        'deep' => [],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'requiredExtrefDeep'  => [
                        [
                            'deep' => [
                                [
                                    'deep' => [
                                        'deep' => [
                                            ['$ref' => 'http://localhost/core/app/admin'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'filled refs' => [
                [
                    'optionalExtref'      => ['$ref' => 'http://localhost/core/app/tablet'],
                    'requiredExtref'      => ['$ref' => 'http://localhost/core/app/admin'],
                    'optionalExtrefArray' => [
                        ['$ref' => 'http://localhost/core/app/tablet'],
                    ],
                    'requiredExtrefArray' => [
                        ['$ref' => 'http://localhost/core/app/admin'],
                    ],
                    'optionalExtrefDeep'  => [
                        [
                            'deep' => [
                                [
                                    'deep' => [
                                        'deep' => [
                                            ['$ref' => 'http://localhost/core/app/tablet'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'requiredExtrefDeep'  => [
                        [
                            'deep' => [
                                [
                                    'deep' => [
                                        'deep' => [
                                            ['$ref' => 'http://localhost/core/app/admin'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            ],
        ];
    }
}
