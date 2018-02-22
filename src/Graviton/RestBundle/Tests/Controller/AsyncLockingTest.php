<?php
/**
 * test for locking feature
 */

namespace Graviton\RestBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use GravitonDyn\TestCaseGroupSerializationBundle\DataFixtures\MongoDB\LoadTestCaseGroupSerializationData;
use React\Promise\Deferred;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class AsyncLockingTest extends RestTestCase
{

    public function testAsyncLock()
    {

        //$i = 0;
        $loop = \React\EventLoop\Factory::create();
        //$deferred = new \React\Promise\Deferred();

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

        $deferred = new \React\Promise\Deferred();
        $promise = $deferred->promise();
        /*
        $promise->done(function($data) use ($statusEntry, $client) {
            $client->put('/event/status/locktest', $statusEntry);
            var_dump($client->getResponse()->getStatusCode());
            echo 'Done: ' . $data . PHP_EOL;
        });
        */

        // [{"op":"replace","path":"/status/0/status","value":"ignored"},{"op":"add","path":"/status/0/action","value":{"$ref":"http://localhost:8000/event/action/filemover-print-default"}}]

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

        $client = static::createRestClient();

        $promise->then(
            null,
            null,
            function($data) use ($client, $patchJson) {
                $client = static::createRestClient();

                $client->request('PATCH', '/event/status/locktest', [], [], [], $patchJson);

                //$client->put('/event/status/locktest', $statusEntry);
                var_dump($client->getResults());
                $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
                var_dump($client->getResponse()->getStatusCode());
                echo 'Done: ' . $data . PHP_EOL;
            }
        );


        /*
        $promise->done(function($data) use ($statusEntry, $client) {
            $client->put('/event/status/locktest', $statusEntry);
            var_dump($client->getResponse()->getStatusCode());
            echo 'Done: ' . $data . PHP_EOL;
        });
        */
        //$deferred->resolve('hello world');

        //$loop->

        $i = 0;
        $loop->addPeriodicTimer(0.01, function(\React\EventLoop\Timer\Timer $timer) use ($deferred, &$i) {
            if ($i < 50) {
                $deferred->notify('hello world');
            } else {
                $deferred->resolve();
                $timer->cancel();
            }
            $i++;

            ob_flush();
        });

        $loop->run();

        // get the item

        var_dump($i);

        $client = static::createRestClient();
        $client->request('GET', '/event/status/locktest');
        $result = $client->getResults();

        var_dump($result);

        //$this->assertSame(50, count($result->status));
        $this->assertSame(50, count($result->information));

        /*
        $timer = $loop->addPeriodicTimer(0.01, function(\React\EventLoop\Timer\Timer $timer) use (&$i, $deferred, $client) {
            $deferred->notify($i++, $client);
            if ($i >= 15) {
                $timer->cancel();
                $deferred->resolve();
            }
        });

        $deferred->promise()->then(function($i, $client) {
            echo 'Done!', PHP_EOL;
        }, null, function($i, $client) {

            $statusEntry = [
                'id' => 'locktest'
            ];

            echo "dude";


            $client->put('/event/status/locktest', $statusEntry);
            var_dump($client->getResponse()->getStatusCode());

        });

        $loop->run();

        */
        /*


        $promise = \Amp\coroutine(function () use ($statusEntry) {

            try {
                $client = static::createRestClient();

                $client->put('/event/status/locktest', $statusEntry);

                return $client;


            } catch (\Exception $e) {
            }
        });

        echo 33; die;








        var_dump($promise);
        die;

        $dude = yield $promise;
        */
        //var_dump($dude);

    }

}
