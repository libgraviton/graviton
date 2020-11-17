<?php
/**
 * functional test for /file
 */

namespace Graviton\FileBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Basic functional test for /file
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class FileControllerTestRestricted extends FileControllerTest
{

    /**
     * custom environment
     *
     * @var string
     */
    protected $environment = 'test_restricted';

    /**
     * custom client options
     *
     * @var string[]
     */
    protected $clientOptions = ['environment' => 'test_restricted'];

    /**
     * checks if restrictions are enforced as they're should be
     *
     * @return mixed
     */
    public function testPostAndGetFileWithClientId()
    {
        $fixtureData = file_get_contents(__DIR__.'/fixtures/test.txt');

        // create a file via PUT
        $client = static::createRestClient($this->clientOptions);
        $client->put(
            '/file/my-test-file-tenant',
            $fixtureData,
            [],
            [],
            ['CONTENT_TYPE' => 'text/plain', 'HTTP_X-GRAVITON-CLIENT' => '555'],
            false
        );
        $this->assertEmpty($client->getResults());
        $response = $client->getResponse();
        $this->assertEquals(204, $response->getStatusCode());

        // combined metadata & file
        $fileMetadata = [
            'id' => 'my-test-file-tenant2',
                'links' => [
                    [
                        '$ref' => 'http://localhost/core/app/2000',
                        'type' => 'someapp'
                    ],
                    [
                        '$ref' => 'http://localhost/core/app/1000',
                        'type' => 'customer'
                    ]
                ]
        ];

        $client = static::createRestClient($this->clientOptions);
        $tempFile = tempnam(__DIR__.'/fixtures/', 'test-');
        file_put_contents($tempFile, 'testcontent');

        $client->put(
            '/file/my-test-file-tenant2',
            null,
            [
                'metadata' => json_encode($fileMetadata),
            ],
            [
                'upload' => new UploadedFile($tempFile, 'test.txt', 'text/plain'),
            ],
            ['HTTP_X-GRAVITON-CLIENT' => '555'],
            false
        );

        $this->assertEmpty($client->getResults());
        $response = $client->getResponse();
        $this->assertEquals(204, $response->getStatusCode());

        // should not be able to get it
        $client = static::createRestClient($this->clientOptions);
        $client->request('GET', '/file/my-test-file-tenant2', [], [], ['HTTP_X-GRAVITON-CLIENT' => '500']);
        $response = $client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());

        $client = static::createRestClient($this->clientOptions);
        $client->post(
            '/file/',
            $fixtureData,
            [],
            [],
            ['CONTENT_TYPE' => 'text/plain', 'HTTP_X-GRAVITON-CLIENT' => '555'],
            false
        );
        $this->assertEmpty($client->getResults());
        $response = $client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());

        $fileLocation = $response->headers->get('Location');

        // get file
        $client = static::createRestClient($this->clientOptions);
        $client->request('GET', $fileLocation, [], [], ['HTTP_X-GRAVITON-CLIENT' => '555']);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        // get metadata
        $client = static::createRestClient($this->clientOptions);
        $client->request(
            'GET',
            $fileLocation,
            [],
            [],
            ['HTTP_ACCEPT' => 'application/json', 'HTTP_X-GRAVITON-CLIENT' => '555']
        );
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        // update metadata (for conditional)
        $data = $client->getResults();
        $data->links = [];
        $data->links[] = [
            '$ref' => 'http://localhost/core/app/2000',
            'type' => 'someapp'
        ];
        $data->links[] = [
            '$ref' => 'http://localhost/core/app/1000',
            'type' => 'customer'
        ];

        $client = static::createRestClient($this->clientOptions);
        $client->put(
            $fileLocation,
            $data,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X-GRAVITON-CLIENT' => '555']
        );

        /*** SHOULD NOT BE ABLE TO GET FILES ***/

        // get metadata
        $client = static::createRestClient($this->clientOptions);
        $client->request(
            'GET',
            $fileLocation,
            [],
            [],
            ['HTTP_ACCEPT' => 'application/json', 'HTTP_X-GRAVITON-CLIENT' => '500']
        );
        $response = $client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());

        // get file
        $client = static::createRestClient($this->clientOptions);
        $client->request('GET', $fileLocation, [], [], ['HTTP_X-GRAVITON-CLIENT' => '500']);
        $response = $client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());

        return $fileLocation;
    }
}
