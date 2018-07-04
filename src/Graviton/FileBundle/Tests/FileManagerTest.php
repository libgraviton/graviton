<?php
/**
 * Test suite for the FileManager
 */

namespace Graviton\FileBundle\Tests;

use Graviton\FileBundle\Manager\FileManager;
use Graviton\TestBundle\Test\RestTestCase;
use GravitonDyn\FileBundle\Document\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File as SfFile;

/**
 * Basic functional test for /file
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class FileManagerTest extends RestTestCase
{
    /** @var FileManager $fileManager */
    private $fileManager;

    /**
     * Initiates mandatory properties
     *
     * @return void
     */
    public function setUp()
    {
        if (!$this->fileManager) {
            $this->fileManager = $this->getContainer()->get('graviton.file.file_manager');
        }
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

        $uploadedFile = $this->getUploadFile('test.txt');
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

        $uploadedFile = $this->getUploadFile('test.txt');
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

        // PUT Lets UPDATE some additional Params
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
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode(), $response->getContent());

        $client = $this->createClient();
        $client->request('GET', $location, [], [], ['HTTP_ACCEPT' => 'application/json']);

        $response = $client->getResponse();
        $contentUpdatedArray = json_decode($response->getContent(), true);
        $modifiedAt = \DateTime::createFromFormat($timeFormat, $contentUpdatedArray['metadata']['modificationDate']);

        $this->assertTrue($modifiedAt > $originalAt, 'File put should have changed modification date and did not');

        // PATCH Lets patch, and time should be changed
        $value->value = 'bValue';
        $patchJson = json_encode(
            array(
                'op' => 'replace',
                'path' => '/metadata/additionalProperties',
                'value' => [$value]
            )
        );
        sleep(1);
        $client = $this->createClient();
        $client->request('PATCH', $location, [], [], [], $patchJson);
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $client->request('GET', $location, [], [], ['HTTP_ACCEPT' => 'application/json']);
        $response = $client->getResponse();
        $contentPatchedArray = json_decode($response->getContent(), true);
        $pacthedAt = \DateTime::createFromFormat($timeFormat, $contentPatchedArray['metadata']['modificationDate']);
        $this->assertTrue($pacthedAt > $modifiedAt, 'File patched should have changed modification date and did not');

        // clean up
        $client = $this->createClient();
        $client->request(
            'DELETE',
            $location
        );
    }


    /**
     * validIdRequest in File Manager, should not be valid to post or put
     *
     * @return void
     */
    public function testPostOrPutIdvalidIdRequestError()
    {
        $document = new File();

        // document
        $shouldFailArray = [
            'fail-not-equal-0' => ['file-id', 'request-id'],
            'fail-not-equal-1' => [''       , 'request-id'],
            'fail-not-equal-2' => ['file-id',  '']
        ];

        foreach ($shouldFailArray as $name => $values) {
            $document->setId($values[0]);
            $requestId = $values[1];

            $method = $this->getPrivateClassMethod(get_class($this->fileManager), 'validIdRequest');
            $result = $method->invokeArgs($this->fileManager, [$document, $requestId]);

            $this->assertFalse($result, $name);
        }
    }

    /**
     * To test standard file upload, just posting a file
     *
     * @return void
     */
    public function testNormalDirectUpload()
    {
        $upload = $this->getUploadFile('test-file.txt');
        $client = $this->createClient();

        $client->request('POST', '/file', [], [$upload]);
        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode(), $response->getContent());
        $location = $response->headers->get('location');

        // Lets get content as JSON
        $client->request('GET', $location, [], [], ['HTTP_ACCEPT' => 'application/json']);
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode(), $response->getContent());
        $contentArray = json_decode($response->getContent(), true);

        // Check contain data
        $this->assertArrayHasKey('modificationDate', $contentArray['metadata']);
        $this->assertArrayHasKey('createDate', $contentArray['metadata']);
        $this->assertArrayHasKey('mime', $contentArray['metadata']);
        $this->assertArrayHasKey('hash', $contentArray['metadata']);
        $this->assertEquals('text/plain', $contentArray['metadata']['mime']);
        $this->assertEquals('test-file.txt', $contentArray['metadata']['filename']);

        $client->request('DELETE', $location, [], [], ['HTTP_ACCEPT' => 'application/json']);
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode(), $response->getContent());

        $client->request('DELETE', $location, [], [], ['HTTP_ACCEPT' => 'application/json']);
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode(), $response->getContent());
    }

    /**
     * Simple function to create a upload file
     *
     * @param string $fileName desired new filename
     * @return UploadedFile
     */
    private function getUploadFile($fileName)
    {
        $newDir = sys_get_temp_dir() . '/'. $fileName;
        copy(__DIR__ . '/Fixtures/test.txt', $newDir);
        $file = new SfFile($newDir);
        return new UploadedFile(
            $file->getRealPath(),
            $fileName,
            $file->getMimeType(),
            $file->getSize()
        );
    }
}
