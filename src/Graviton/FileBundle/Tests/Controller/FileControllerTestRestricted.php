<?php
/**
 * functional test for /file
 */

namespace Graviton\FileBundle\Tests\Controller;

use Graviton\LinkHeaderParser\LinkHeader;
use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

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

    public function testPostAndGetFileWithClientId()
    {
        $fixtureData = file_get_contents(__DIR__.'/fixtures/test.txt');

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

        // get metadata
        $client = static::createRestClient($this->clientOptions);
        $client->request('GET', $fileLocation, [], [], ['HTTP_ACCEPT' => 'application/json', 'HTTP_X-GRAVITON-CLIENT' => '500']);
        $response = $client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());

        // get file
        $client = static::createRestClient($this->clientOptions);
        $client->request('GET', $fileLocation, [], [], ['HTTP_X-GRAVITON-CLIENT' => '500']);
        $response = $client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());

        // find them..

        // get metadata
        $client = static::createRestClient($this->clientOptions);
        $client->request('GET', $fileLocation, [], [], ['HTTP_ACCEPT' => 'application/json', 'HTTP_X-GRAVITON-CLIENT' => '555']);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        // get file
        $client = static::createRestClient($this->clientOptions);
        $client->request('GET', $fileLocation, [], [], ['HTTP_X-GRAVITON-CLIENT' => '555']);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }
}
