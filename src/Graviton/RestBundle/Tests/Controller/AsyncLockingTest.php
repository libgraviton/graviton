<?php
/**
 * test for locking feature
 */

namespace Graviton\RestBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class AsyncLockingTest extends RestTestCase
{

    /**
     * try to send 30 async requests to /event/status and see if the result is consistent
     *
     * @return void
     */
    public function testAsyncLock()
    {
        /**
         * create test entry
         */
        $client = static::createRestClient();
        $statusEntry = new \stdClass();
        $statusEntry->id = 'locktest';
        $statusEntry->status = [];
        $defaultStatus = new \stdClass();
        $defaultStatus->workerId = 'hans';
        $defaultStatus->status = 'opened';
        $statusEntry->status[] = $defaultStatus;

        $client->put('/event/status/locktest', $statusEntry);
        $this->assertSame(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        /**
         * the patch we will send
         */
        $patchJson = json_encode(
            [
                [
                    'op' => 'replace',
                    'path' => '/status/0/status',
                    'value' => 'ignored'
                ],
                [
                    'op' => 'add',
                    'path' => '/status/0/action',
                    'value' => [
                        '$ref' => 'http://localhost/event/action/default'
                    ]
                ],
                [
                    'op' => 'add',
                    'path' => '/information/-',
                    'value' => [
                        'workerId' => 'hans',
                        'type' => 'info',
                        'content' => 'addedInfo'
                    ]
                ]
            ]
        );

        $promiseStack = [];
        $deferred = new \React\Promise\Deferred();

        $i = 0;
        while ($i < 30) {
            $promise = $deferred->promise();

            $promise->then(
                function () use ($patchJson) {
                    $client = static::createRestClient();
                    $client->request('PATCH', '/event/status/locktest', [], [], [], $patchJson);
                    $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
                }
            );

            $promiseStack[] = $promise;
            $i++;
        }

        \React\Promise\all($promiseStack)->then(
            function () {
                // after all is done; check entry
                $client = static::createRestClient();
                $client->request('GET', '/event/status/locktest');
                $result = $client->getResults();

                $this->assertSame(30, count($result->information));
                $this->assertSame(1, count($result->status));
            }
        );

        // start the whole thing
        $deferred->resolve();
    }
}
