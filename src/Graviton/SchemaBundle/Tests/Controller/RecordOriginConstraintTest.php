<?php
/**
 * test for RecordOriginConstraint
 */

namespace Graviton\SchemaBundle\Tests\ConstraintBuilder;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class RecordOriginConstraintTest extends RestTestCase
{

    /**
     * @TODO Upsert via POST, stuff via PATCH
     */

    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp()
    {
        $this->loadFixtures(
            array(
                'GravitonDyn\CustomerBundle\DataFixtures\MongoDB\LoadCustomerData',
            ),
            null,
            'doctrine_mongodb'
        );
    }

    /**
     * test the validation of the RecordOriginConstraint
     *
     * @dataProvider createDataProvider
     *
     * @param string  $entity           To be found
     * @param integer $expectedStatus   Header status code
     * @param string  $expectedResponse Post data result of post
     *
     * @return void
     */
    public function testRecordOriginHandlingOnCreate($entity, $expectedStatus, $expectedResponse)
    {
        $client = static::createRestClient();
        $client->post('/person/customer/', $entity);

        $response = $client->getResponse();
        $this->assertEquals($expectedStatus, $response->getStatusCode());
        $this->assertEquals($expectedResponse, $client->getResults());
    }

    /**
     * Test the validation of the RecordOriginConstraint
     *
     * @param string  $fieldsToSet      Field used in query params
     * @param integer $expectedStatus   Header status code
     * @param string  $expectedResponse Result to be returned
     * @param boolean $checkSavedEntry  To check db for correct result
     *
     * @dataProvider updateDataProvider
     *
     * @return void
     */
    public function testRecordOriginHandlingOnUpdate(
        $fieldsToSet,
        $expectedStatus,
        $expectedResponse,
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
        $this->assertEquals($expectedResponse, $client->getResults());

        if ($checkSavedEntry) {
            // fetch it again and compare
            $client = static::createRestClient();
            $client->request('GET', '/person/customer/100');
            $this->assertEquals($expectedObject, $client->getResults());
        }
    }

    /**
     * Create the data provider used for test
     *
     * @return array
     */
    public function createDataProvider()
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
                'httpStatusExpected' => Response::HTTP_CREATED,
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
                'httpStatusExpected' => Response::HTTP_BAD_REQUEST,
                'expectedResponse' => [
                    (object) [
                        'propertyPath' => 'recordOrigin',
                        'message' => 'Creating documents with the recordOrigin field having a '.
                            'value of core is not permitted.'
                    ]
                ]
            ]
        ];
    }

    /**
     * During work data will be updated and uses this function
     *
     * @return array
     */
    public function updateDataProvider()
    {
        $expectedErrorOutput = [
            (object) [
                'propertyPath' => 'recordOrigin',
                'message' => 'Prohibited modification attempt on record with recordOrigin of core'
            ]
        ];

        return [

            /*** STUFF THAT SHOULD BE ALLOWED ***/
            'create-allowed-object' => [
                'fieldsToSet' => [
                    'addedField' => (object) [
                        'some' => 'property',
                        'another' => 'one'
                    ]
                ],
                'httpStatusExpected' => Response::HTTP_NO_CONTENT,
                'expectedResponse' => null
            ],
            'subproperty-modification' => [
                'fieldsToSet' => [
                    'someObject' => (object) [
                        'oneField' => 'value',
                        'twoField' => 'twofield'
                    ]
                ],
                'httpStatusExpected' => Response::HTTP_NO_CONTENT,
                'expectedResponse' => null
            ],

            /*** STUFF THAT NEEDS TO BE DENIED ***/
            'denied-subproperty-modification' => [
                'fieldsToSet' => [
                    'someObject' => (object) [
                        'oneField' => 'changed-value'
                    ]
                ],
                'httpStatusExpected' => Response::HTTP_BAD_REQUEST,
                'expectedResponse' => $expectedErrorOutput,
                'checkSavedEntry' => false
            ],
            'denied-try-change-recordorigin' => [
                'fieldsToSet' => [
                    'recordOrigin' => 'hans'
                ],
                'httpStatusExpected' => Response::HTTP_BAD_REQUEST,
                'expectedResponse' => $expectedErrorOutput,
                'checkSavedEntry' => false
            ],
        ];
    }
}
