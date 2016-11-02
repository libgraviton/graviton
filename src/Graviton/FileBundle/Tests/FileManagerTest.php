<?php
/**
 * Test suite for the FileManager
 */

namespace Graviton\FileBundle\Tests;

use Graviton\FileBundle\FileManager;
use Graviton\TestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * Basic functional test for /file
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class FileManagerTest extends WebTestCase
{
    /** @var \Gaufrette\Filesystem $fileSystem */
    private $fileSystem;

    /** @var \Graviton\FileBundle\FileDocumentFactory $fileDocumentFactory */
    private $fileDocumentFactory;

    /**
     * Initiates mandatory properties
     *
     * @return void
     */
    public function setUp()
    {
        $this->fileSystem = $this->getMockBuilder('\Gaufrette\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileDocumentFactory = $this->getMockBuilder('\Graviton\FileBundle\FileDocumentFactory')
            ->getMock();
    }

    /**
     * Verifies the correct behavior of has method
     *
     * @return void
     */
    public function testHas()
    {
        $this->fileSystem
            ->expects($this->once())
            ->method('has')
            ->willReturn(true);

        $manager = new FileManager($this->fileSystem, $this->fileDocumentFactory);

        $this->assertTrue($manager->has('myKey'));
    }

    /**
     * Verifies the correct behavior of read method
     *
     * @return void
     */
    public function testRead()
    {
        $this->fileSystem
            ->expects($this->once())
            ->method('read')
            ->willReturn('myData');

        $manager = new FileManager($this->fileSystem, $this->fileDocumentFactory);

        $this->assertEquals('myData', $manager->read('myKey'));
    }

    /**
     * Verifies the correct behavior of read method
     *
     * @return void
     */
    public function testDelete()
    {
        $this->fileSystem
            ->expects($this->once())
            ->method('delete')
            ->willReturn(true);

        $manager = new FileManager($this->fileSystem, $this->fileDocumentFactory);

        $this->assertTrue($manager->delete('myKey'));
    }

    /**
     * Verifies the correct behavior of the FileManager
     *
     * @return void
     */
    public function testSaveFiles()
    {
        $jsonData = '{
          "links": [
            {
              "$ref": "http://localhost/testcase/readonly/101",
              "type": "owner"
            },
            {
              "$ref": "http://localhost/testcase/readonly/102",
              "type": "module"
            }
          ],
          "metadata": {
            "action":[{"command":"print"},{"command":"archive"}]
          }
        }';

        copy(__DIR__ . '/Fixtures/test.txt', sys_get_temp_dir() . '/test.txt');
        $file = sys_get_temp_dir() . '/test.txt';
        $uploadedFile = new UploadedFile($file, 'test.txt', 'text/plain', 15);
        $client = $this->createClient();
        $client->request(
            'POST',
            '/file',
            [
                'metadata' => $jsonData,
            ],
            [
                'upload' => $uploadedFile,
            ]
        );
        $response = $client->getResponse();
        $location = $response->headers->get('location');

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertContains('/file/', $location);

        // receive generated file information
        $client = $this->createClient();
        $client->request(
            'GET',
            $location,
            [],
            [],
            [
                'HTTP_ACCEPT' => 'application/json'
            ]
        );

        $response = $client->getResponse();
        $contentArray = json_decode($response->getContent(), true);

        $this->assertEquals([["command" => "print"], ["command" => "archive"]], $contentArray['metadata']['action']);
        $this->assertJsonStringEqualsJsonString(
            '[
              {
                "$ref": "http://localhost/testcase/readonly/101",
                "type": "owner"
              },
              {
                "$ref": "http://localhost/testcase/readonly/102",
                "type": "module"
              }
            ]',
            json_encode($contentArray['links'])
        );

        // Check contain data
        $this->assertArrayHasKey('modificationDate', $contentArray['metadata']);
        $this->assertArrayHasKey('createDate', $contentArray['metadata']);

        // Test Metadata, and Remove date.
        unset($contentArray['metadata']['modificationDate']);
        unset($contentArray['metadata']['createDate']);

        $this->assertJsonStringEqualsJsonString(
            '{
                "size":16,
                "action":[
                    {"command":"print"},
                    {"command":"archive"}
                ],
                "mime":"text\/plain",
                "filename":"test.txt",
                "hash":"4f3cbec0e58903d8bdcbd03d283cf43ed49a95d8d8b341ee38c0ba085204e2d5",
                "additionalProperties":[]
             }',
            json_encode($contentArray['metadata'])
        );

        // clean up
        $client = $this->createClient();
        $client->request(
            'DELETE',
            $location
        );
    }


    /**
     * Verifies the correct behavior of the FileManager
     *
     * @return void
     */
    public function testUpdateFiles()
    {
        $timeFormat = 'Y-m-d\TH:i:sP';

        $jsonData = '{
          "links": [
            {
              "$ref": "http://localhost/testcase/readonly/101",
              "type": "owner"
            },
            {
              "$ref": "http://localhost/testcase/readonly/102",
              "type": "module"
            }
          ],
          "metadata": {
            "action":[{"command":"print"},{"command":"archive"}]
          }
        }';

        copy(__DIR__ . '/Fixtures/test.txt', sys_get_temp_dir() . '/test.txt');
        $file = sys_get_temp_dir() . '/test.txt';
        $uploadedFile = new UploadedFile($file, 'test.txt', 'text/plain', 15);
        $client = $this->createClient();
        $client->request(
            'POST',
            '/file',
            [
                'metadata' => $jsonData,
            ],
            [
                'upload' => $uploadedFile,
            ]
        );
        $response = $client->getResponse();
        $location = $response->headers->get('location');

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertContains('/file/', $location);

        // receive generated file information
        $client = $this->createClient();
        $client->request('GET', $location, [], [], ['HTTP_ACCEPT' => 'application/json']);

        $response = $client->getResponse();
        $contentArray = json_decode($response->getContent(), true);

        // Check contain data
        $this->assertArrayHasKey('modificationDate', $contentArray['metadata']);
        $this->assertArrayHasKey('createDate', $contentArray['metadata']);
        // Test Metadata, and Remove date.
        $originalAt = \DateTime::createFromFormat($timeFormat, $contentArray['metadata']['modificationDate']);
        unset($contentArray['metadata']['modificationDate']);
        unset($contentArray['metadata']['createDate']);

        // Lets UPDATE some additional Params, PATCH
        $client = $this->createClient();
        $value = new \stdClass();
        $value->name = 'aField';
        $value->value = 'aValue';
        $contentArray['metadata']['additionalProperties'] = [$value];
        sleep(1);
        $client->request(
            'PUT',
            $location,
            [
                'metadata' => json_encode($contentArray),
            ]
        );
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $client = $this->createClient();
        $client->request('GET', $location, [], [], ['HTTP_ACCEPT' => 'application/json']);

        $response = $client->getResponse();
        $contentUpdatedArray = json_decode($response->getContent(), true);
        $modifiedAt = \DateTime::createFromFormat($timeFormat, $contentUpdatedArray['metadata']['modificationDate']);

        $this->assertTrue($modifiedAt > $originalAt, 'File uploaded in put should have changed mod date and did not');

        // clean up
        $client = $this->createClient();
        $client->request(
            'DELETE',
            $location
        );
    }
}
