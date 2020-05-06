<?php
/**
 * test for all the events the RestrictionListener has
 */

namespace Graviton\RestBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
// phpcs:disable
use GravitonDyn\TestCaseRestListenerCondPersisterEntityBundle\DataFixtures\MongoDB\LoadTestCaseRestListenerCondPersisterEntityData;
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
    protected $environment = 'test_restricted_conditional';

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
     * @param int   $entityId         entity id
     * @param mixed $expectedClientId client id that should be persisted
     *
     * @return void
     */
    public function testConditionalPersisting($serverParameters, $entityId, $expectedClientId)
    {
        $record = new \stdClass();
        $record->id = $entityId;
        $record->stringField = "hans";
        $record->entityId = $entityId;

        $client = static::createRestClient($this->clientOptions);
        $client->put('/testcase/rest-listeners-cond-persister/'.$entityId, $record, [], [], $serverParameters);

        $client = static::createRestClient($this->clientOptions);
        $client->request('GET', '/testcase/rest-listeners-cond-persister/'.$entityId, [], [], $serverParameters);
        $result = $client->getResults();

        if ($expectedClientId === null) {
            $this->assertObjectNotHasAttribute('clientId', $result);
        } else {
            $this->assertEquals($expectedClientId, $result->clientId);
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
                ['HTTP_X-GRAVITON-CLIENT' => '5'],
                '1000',
                5
            ],
            // entityId 2000 is not restricted
            'client10' => [
                ['HTTP_X-GRAVITON-CLIENT' => '10'],
                '2000',
                null
            ],
            'client999' => [
                ['HTTP_X-GRAVITON-CLIENT' => '999'],
                '3000',
                999
            ]
        ];
    }
}
