<?php
/**
 * functional test for /event/status
 */

namespace Graviton\Tests\Rest\Controller;

use Graviton\RestBundle\MessageProducer\Dummy;
use Graviton\Tests\RestTestCase;
use Laminas\Diactoros\Uri;
use Symfony\Component\HttpFoundation\Response;

/**
 * Functional test for /event/status.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class FileControllerUnavailableTest extends RestTestCase
{

    /**
     * custom environment
     *
     * @var string
     */
    protected static $environment = 'test_file_unavailable';

    /**
     * custom client options
     *
     * @var string[]
     */
    private $clientOptions = ['environment' => 'test_file_unavailable'];

    /**
     * setup
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->loadFixturesLocal([]);
    }

    /**
     * if file backend is available, no entry should be created!
     *
     * @return void
     */
    public function testCreateFileWhileUnavailable()
    {
        $client = static::createRestClient($this->clientOptions);

        $newContent = file_get_contents(__DIR__.'/resources/testpicture.jpg');
        $fileId = uniqid('grv');

        $client->put(
            '/file/'.$fileId,
            $newContent,
            [],
            [],
            ['CONTENT_TYPE' => 'image/jpeg'],
            false
        );

        $response = $client->getResponse();

        // should be 500!
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());

        // file should not exist!
        $client = static::createRestClient($this->clientOptions);
        $client->request(
            'GET',
            '/file/'.$fileId
        );

        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }
}
