<?php
namespace App;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;

require_once __DIR__.'/../vendor/autoload.php';

class VarnishTest extends TestCase {

    public function testEventStatusVarnish() {
        $url = 'http://gateway.vcap.me';
        $restToken = '';
        $headers = [
            'x-rest-token' => $restToken,
            'content-type' => 'application/json'
        ];

        $client = HttpClient::create();

        $newItem = '{
            "createDate": "2020-06-15T08:46:19+0200",
            "eventName": "hans",
            "userId": "anonymous",
            "eventResource": {
                "$ref": "http://api.vcap.me/ssp/action/5ee7193bbada78565d735803"
            },
            "status": [
                {
                    "workerId": "ssp",
                    "status": "opened"
                }
            ],
            "information": []
        }';

        $errorOnFirstFetchCounter = 0;
        $errorOnSecondFetchCounter = 0;

        $i = 0;
        while ($i < 1) {
            $response = $client->request(
                'POST',
                $url . '/event/status/',
                [
                    'headers' => $headers,
                    'body' => $newItem
                ]
            );
            $postHeaders = $response->getHeaders();
            $location = $postHeaders['location'][0];

            $getResponse = $client->request(
                'GET',
                $url . $location,
                [
                    'headers' => $headers
                ]
            );

            $this->assertSame(200, $getResponse->getStatusCode());
            $this->assertStringContainsString('opened', $getResponse->getContent());

            // PATCH STATUS
            $patchItem = '
            [
             { "op": "replace", "path": "/status/0/status", "value": "working" }
            ]
        ';
            $client->request(
                'PATCH',
                $url . $location,
                [
                    'headers' => $headers,
                    'body' => $patchItem
                ]
            );

            $getResponse = $client->request(
                'GET',
                $url . $location,
                [
                    'headers' => $headers
                ]
            );

            $this->assertSame(200, $getResponse->getStatusCode());
            if (strpos($getResponse->getContent(), 'working') === false) {
                $errorOnFirstFetchCounter++;

                echo 'error on first fetch!' . PHP_EOL;
                sleep(1);

                $getResponse = $client->request(
                    'GET',
                    $url . $location,
                    [
                        'headers' => $headers
                    ]
                );

                if (strpos($getResponse->getContent(), 'working') === false) {
                    $errorOnSecondFetchCounter++;
                }
            }
            $client->request(
                'DELETE',
                $url . $location,
                [
                    'headers' => $headers
                ]
            );
            $getResponse = $client->request(
                'GET',
                $url . $location,
                [
                    'headers' => $headers
                ]
            );
            $this->assertSame(404, $getResponse->getStatusCode());

            $i++;
        }

        echo "errors first fetch: ".$errorOnFirstFetchCounter.PHP_EOL.'second fetch: '.$errorOnSecondFetchCounter.PHP_EOL;
    }
}


