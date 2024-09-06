<?php
/**
 * functional test for /file
 */

namespace Graviton\Tests\Rest\Controller;

/**
 * Basic functional test for /file
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class FileControllerRestrictedConditionalTest extends FileControllerRestrictedTest
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
    protected $clientOptions = ['environment' => 'test_restricted_conditional'];

    /**
     * checks if restrictions are enforced as they're should be
     *
     * @return mixed
     */
    public function testPostAndGetFileWithClientId()
    {
        $fileLocation = parent::testPostAndGetFileWithClientId();

        // ***** try to make it unrestricted again

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
            '$ref' => 'http://localhost/core/app/2000',
            'type' => 'customer'
        ];


        $data->links[1]['$ref'] = 'http://localhost/core/app/2000';
        $client = static::createRestClient($this->clientOptions);
        $client->put(
            $fileLocation,
            $data,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X-GRAVITON-CLIENT' => '555']
        );

        // now we should be able to get it again...
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
        $this->assertEquals(200, $response->getStatusCode());

        // get file
        $client = static::createRestClient($this->clientOptions);
        $client->request(
            'GET',
            $fileLocation,
            [],
            [],
            ['HTTP_X-GRAVITON-CLIENT' => '500']
        );
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        // update file
        $client = static::createRestClient($this->clientOptions);
        $client->put(
            $fileLocation,
            'new content',
            [],
            [],
            ['CONTENT_TYPE' => 'text/plain', 'HTTP_X-GRAVITON-CLIENT' => '555'],
            false
        );
        $this->assertEmpty($client->getResults());
        $response = $client->getResponse();
        $this->assertEquals(204, $response->getStatusCode());
    }
}
