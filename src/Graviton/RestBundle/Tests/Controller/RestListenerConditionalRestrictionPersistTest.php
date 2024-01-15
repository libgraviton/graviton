<?php
/**
 * test for all the events the RestrictionListener has
 */

namespace Graviton\RestBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
// phpcs:disable
use GravitonDyn\TestCaseRestListenerCondPersisterEntityBundle\DataFixtures\MongoDB\LoadTestCaseRestListenerCondPersisterEntityData;
use Symfony\Component\HttpFoundation\Response;

// phpcs:enable

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RestListenerConditionalRestrictionPersistTest extends RestTestCase
{

    /**
     * custom environment
     *
     * @var string
     */
    protected static $environment = 'test_restricted_conditional';

    /**
     * custom client options
     *
     * @var string[]
     */
    private $clientOptions = ['environment' => 'test_restricted_conditional'];

    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->loadFixturesLocal(
            [
                LoadTestCaseRestListenerCondPersisterEntityData::class
            ]
        );
    }

    /**
     * test the conditional persisting
     *
     * @dataProvider fetchDataProvider
     *
     * @param array $serverParameters server params
     * @param array $condHeader       conditional headers
     * @param int   $entityId         entity id
     * @param mixed $expectedClientId client id that should be persisted
     *
     * @return void
     */
    public function testConditionalPersisting($serverParameters, $condHeader, $entityId, $expectedClientId)
    {
        $record = new \stdClass();
        $record->id = $entityId;
        $record->stringField = "hans";
        $record->entityId = $entityId;

        $client = static::createRestClient($this->clientOptions);
        // to persist, we send both restriction headers -> but 'moreField' will be set by the fixed value!
        $client->put(
            '/testcase/rest-listeners-cond-persister/'.$entityId,
            $record,
            [],
            [],
            array_merge(
                $serverParameters,
                $condHeader
            )
        );

        // now we GET it with just the clientid header
        $client = static::createRestClient($this->clientOptions);
        $client->request('GET', '/testcase/rest-listeners-cond-persister/'.$entityId, [], [], $serverParameters);
        $result = $client->getResults();

        // client should be set normal
        if ($expectedClientId == null) {
            $this->assertObjectNotHasProperty('clientId', $result);
            $this->assertObjectNotHasProperty('moreField', $result);
        } else {
            $this->assertEquals($expectedClientId, $result->clientId);
            // make sure the 'moreField' is set to the configured fixed value, not to the header we've sent!
            $this->assertEquals(123456, $result->moreField);
        }

        // now, we GET it again with both headers,
        // should be a 404 by the restriction or found if no restriction was added
        $client = static::createRestClient($this->clientOptions);
        $client->request(
            'GET',
            '/testcase/rest-listeners-cond-persister/'.$entityId,
            [],
            [],
            array_merge(
                $serverParameters,
                $condHeader
            )
        );

        if ($expectedClientId == null) {
            // should be found as no restrictions were added
            $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        } else {
            // should not be found!
            $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
        }
    }

    /**
     * data provider for data fetching tests..
     *
     * @return array data
     */
    public function fetchDataProvider()
    {
        return [
            'client5' => [
                [
                    'HTTP_X-GRAVITON-CLIENT' => '5'
                ],
                [
                    'HTTP_X-GRAVITON-MOREFIELD' => '5'
                ],
                '1000',
                5
            ],
            // entityId 2000 is not restricted
            'client10' => [
                [
                    'HTTP_X-GRAVITON-CLIENT' => '10'
                ],
                [
                    'HTTP_X-GRAVITON-MOREFIELD' => '10'
                ],
                '2000',
                null
            ],
            'client999' => [
                [
                    'HTTP_X-GRAVITON-CLIENT' => '999'
                ],
                [
                    'HTTP_X-GRAVITON-MOREFIELD' => '999'
                ],
                '3000',
                999
            ]
        ];
    }
}
