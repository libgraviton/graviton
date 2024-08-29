<?php
/**
 * test for RecordOriginConstraint
 */

namespace Graviton\Tests\Rest\Controller;

use Graviton\Tests\RestTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RecordOriginBodyCheckerTest extends RestTestCase
{

    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->loadFixturesLocal(
            array(
                'GravitonDyn\EmbedTestEntityBundle\DataFixtures\MongoDB\LoadEmbedTestEntityData',
                'GravitonDyn\CustomerBundle\DataFixtures\MongoDB\LoadCustomerData',
            )
        );
    }

    /**
     * test the validation of the RecordOriginConstraint
     *
     * @param object  $entity           The object to create
     * @param integer $expectedStatus   Header status code
     * @param string  $expectedResponse Post data result of post
     *
     * @return void
     */
    #[DataProvider('createDataProvider')]
    public function testRecordOriginHandlingOnCreate($entity, $expectedStatus, $expectedResponse)
    {
        $client = static::createRestClient();
        $client->post('/person/customer/', $entity);

        $response = $client->getResponse();
        $this->assertEquals($expectedStatus, $response->getStatusCode());
        $this->assertEquals($expectedResponse, $client->getResults());
    }

    /**
     * tests for the case if user doesn't provide an id in payload.. constraint
     * must take the id from the request in that case.
     *
     * @return void
     */
    public function testRecordOriginHandlingWithNoIdInPayload()
    {
        $record = (object) [
            //'id' => '' - no, no id.. that's the point ;-)
            'customerNumber' => 555,
            'name' => 'Muster Hans',
            'subArray' => [
                [
                    'oneField' => 'one',
                    'twoField' => 'two'
                ]
            ]
        ];

        $client = static::createRestClient();
        $client->put('/person/customer/100', $record);

        $this->assertStringContainsString(
            'are allowed to be modified in this service',
            $client->getResults()[0]->message
        );

        $this->assertEquals(1, count($client->getResults()));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
    }

    /**
     * Test the validation of the RecordOriginConstraint
     *
     * @param array   $fieldsToSet     Fields to be modified
     * @param integer $expectedStatus  Header status code
     * @param boolean $checkSavedEntry To check db for correct result
     *
     * @dataProvider updateDataProvider
     *
     * @return void
     */
    public function testRecordOriginHandlingOnUpdate(
        $fieldsToSet,
        $expectedStatus,
        $checkSavedEntry = true
    ) {
        $client = static::createRestClient();
        $client->request('GET', '/person/customer/100');
        $result = $client->getResults();

        // apply changes
        foreach ($fieldsToSet as $key => $val) {
            $result->{$key} = $val;
        }

        $expectedObject = $result;

        $client = static::createRestClient();
        $client->put('/person/customer/100', $result);

        $response = $client->getResponse();
        $this->assertEquals($expectedStatus, $response->getStatusCode());

        if ($checkSavedEntry) {
            // fetch it again and compare
            $client = static::createRestClient();
            $client->request('GET', '/person/customer/100');
            $this->assertEquals($expectedObject, $client->getResults());
        }
    }

    /**
     * Test the validation of the RecordOriginConstraint
     *
     * @param array   $ops            PATCH operations
     * @param integer $expectedStatus Header status code
     * @param ?array $checkSomeFields field checks
     *
     * @dataProvider patchDataProvider
     *
     * @return void
     */
    public function testRecordOriginHandlingOnPatch(
        $ops,
        $expectedStatus,
        ?array $checkSomeFields = null
    ) {
        $original = ini_get('date.timezone');
        ini_set('date.timezone', 'Asia/Kuala_Lumpur');

        $client = static::createRestClient();

        $client->request('PATCH', '/person/customer/100', [], [], [], json_encode($ops));

        $response = $client->getResponse();
        $this->assertEquals($expectedStatus, $response->getStatusCode());

        if (!is_null($checkSomeFields)) {
            $client = static::createRestClient();
            $client->request('GET', '/person/customer/100');
            $results = $client->getResults();

            foreach ($checkSomeFields as $name => $value) {
                $this->assertEquals(
                    $value,
                    $results->{$name},
                    "property {$value} should match."
                );
            }
        }

        ini_set('date.timezone', $original);
    }

    /**
     * test to see if DELETE on a recordorigin: core is denied
     *
     * @return void
     */
    public function testDeleteHandling()
    {
        $client = static::createRestClient();
        $client->request('DELETE', '/person/customer/100');
        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertStringContainsString(
            "Unable to delete this record, protected recordOrigin.",
            $client->getResults()[0]->message
        );
        $this->assertStringContainsString(
            "recordOrigin",
            $client->getResults()[0]->propertyPath
        );
    }

    /**
     * Data provider for POST related stuff
     *
     * @return array
     */
    public static function createDataProvider(): array
    {
        $baseObj = [
            'customerNumber' => 888,
            'name' => 'Muster Hans'
        ];

        return [

            /*** STUFF THAT SHOULD BE ALLOWED ***/

            'create-allowed-object' => [
                'entity' => (object) array_merge(
                    $baseObj,
                    [
                        'recordOrigin' => 'hans'
                    ]
                ),
                'expectedStatus' => Response::HTTP_CREATED,
                'expectedResponse' => null
            ],

            /*** STUFF THAT SHOULD BE DENIED ***/

            'create-recordorigin-core' => [
                'entity' => (object) array_merge(
                    $baseObj,
                    [
                        'recordOrigin' => 'core'
                    ]
                ),
                'expectedStatus' => Response::HTTP_BAD_REQUEST,
                'expectedResponse' => [
                    (object) [
                        'propertyPath' => 'recordOrigin',
                        'message' => 'It is not allowed to create records with recordOrigin values "core"'
                    ]
                ]
            ]
        ];
    }

    /**
     * Data provider for PUT related stuff
     *
     * @return array
     */
    public static function updateDataProvider(): array
    {
        return [

            /*** STUFF THAT SHOULD BE ALLOWED ***/
            'embed-modification' => [
                'fieldsToSet' => [
                    'entity' => (object) [
                        "id" => "two",
                        "data" => "two"
                    ]
                ],
                'expectedStatus' => Response::HTTP_NO_CONTENT
            ],
            'ref-array-modification' => [
                'fieldsToSet' => [
                    'entities' => [
                        (object) [
                            "id" => "three",
                            "data" => "three"
                        ]
                    ]
                ],
                'expectedStatus' => Response::HTTP_NO_CONTENT
            ],
            'create-allowed-object' => [
                'fieldsToSet' => [
                    'addedField' => (object) [
                        'some' => 'property',
                        'another' => 'one'
                    ]
                ],
                'expectedStatus' => Response::HTTP_NO_CONTENT
            ],
            'subproperty-modification' => [
                'fieldsToSet' => [
                    'someObject' => (object) [
                        'oneField' => 'value',
                        'twoField' => 'twofield'
                    ]
                ],
                'expectedStatus' => Response::HTTP_NO_CONTENT
            ],

            /*** STUFF THAT NEEDS TO BE DENIED ***/
            'denied-subproperty-modification' => [
                'fieldsToSet' => [
                    'someObject' => (object) [
                        'oneField' => 'changed-value'
                    ]
                ],
                'expectedStatus' => Response::HTTP_BAD_REQUEST,
                'checkSavedEntry' => false
            ],
            'denied-try-change-recordorigin' => [
                'fieldsToSet' => [
                    'recordOrigin' => 'hans'
                ],
                'expectedStatus' => Response::HTTP_BAD_REQUEST,
                'checkSavedEntry' => false
            ],
        ];
    }

    /**
     * Data provider for PATCH related stuff
     *
     * @return array
     */
    public static function patchDataProvider(): array
    {
        return [

            /*** STUFF THAT SHOULD BE ALLOWED ***/
            'patch-embedded' => [
                'ops' => [
                    [
                        'op' => 'replace',
                        'path' => '/entity',
                        'value' => [
                            'id' => 'two',
                            'data' => 'two'
                        ]
                    ]
                ],
                'expectedStatus' => Response::HTTP_OK
            ],
            'patch-embed-array' => [
                'ops' => [
                    [
                        'op' => 'replace',
                        'path' => '/entities',
                        'value' => [
                            (object) [
                                'id' => 'three',
                                'data' => 'three'
                            ]
                        ]
                    ]
                ],
                'expectedStatus' => Response::HTTP_OK,
                'checkSomeFields' => [
                    'entities' => [
                        (object) [
                            'id' => 'three',
                            'data' => 'three'
                        ]
                    ]
                ]
            ],
            'patch-embed-array-element' => [
                'ops' => [
                    [
                        'op' => 'replace',
                        'path' => '/entities/1',
                        'value' => (object) [
                                'id' => 'three',
                                'data' => 'three'
                        ]
                    ]
                ],
                'expectedStatus' => Response::HTTP_OK,
                'checkSomeFields' => [
                    'entities' => [
                        (object) [
                            'id' => 'one',
                            'data' => 'one'
                        ],
                        (object) [
                            'id' => 'three',
                            'data' => 'three'
                        ]
                    ]
                ]
            ],
            'patch-allowed-attribute' => [
                'ops' => [
                    [
                        'op' => 'add',
                        'path' => '/someObject/twoField',
                        'value' => 'myValue'
                    ]
                ],
                'expectedStatus' => Response::HTTP_OK
            ],
            'patch-add-object-data' => [
                'ops' => [
                    [
                        'op' => 'add',
                        'path' => '/addedField',
                        'value' => [
                            'someProperty' => 'someValue',
                            'anotherOne' => 'oneMore'
                        ]
                    ]
                ],
                'expectedStatus' => Response::HTTP_OK
            ],

            /*** STUFF THAT NEEDS TO BE DENIED ***/
            'patch-denied-subproperty' => [
                'ops' => [
                    [
                        'op' => 'add',
                        'path' => '/someObject/oneField',
                        'value' => 'myValue'
                    ]
                ],
                'expectedStatus' => Response::HTTP_BAD_REQUEST
            ],
            'patch-denied-recordorigin-change' => [
                'ops' => [
                    [
                        'op' => 'replace',
                        'path' => '/recordOrigin',
                        'value' => 'hans'
                    ]
                ],
                'expectedStatus' => Response::HTTP_BAD_REQUEST
            ],
            'patch-ref-array-with-dbref' => [
                'ops' => [
                    [
                        'op' => 'replace',
                        'path' => '/entities',
                        'value' => [
                            [
                                '$ref' => 'EmbedTestEntity',
                                '$id' => 'two'
                            ]
                        ]
                    ]
                ],
                'expectedStatus' => Response::HTTP_BAD_REQUEST
            ]
        ];
    }
}
