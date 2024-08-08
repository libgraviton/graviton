<?php
/**
 * functional test for /file
 */

namespace Graviton\Tests\Rest\Controller;

// @codingStandardsIgnoreStart
use Graviton\Tests\RestTestCase;
use GravitonDyn\FileBundle\DataFixtures\MongoDB\LoadFileData;
use GravitonDyn\TestCaseRestListenerCondPersisterEntityBundle\DataFixtures\MongoDB\{
    LoadTestCaseRestListenerCondPersisterEntityData};
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

// @codingStandardsIgnoreEnd

/**
 * Basic functional test for /file
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class FileControllerTest extends RestTestCase
{

    /**
     * custom environment
     *
     * @var string
     */
    protected static $environment = 'test';

    /**
     * custom client options
     *
     * @var string[]
     */
    protected $clientOptions = ['environment' => 'test'];

    /**
     * setup client and load fixtures
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->loadFixturesLocal(
            [
                LoadFileData::class,
                LoadTestCaseRestListenerCondPersisterEntityData::class
            ]
        );
    }

    /**
     * check for empty collections when no fixtures are loaded
     *
     * @return void
     */
    public function testFindAllEmptyCollection()
    {
        // reset fixtures since we already have some from setUp
        $this->loadFixturesLocal([]);
        $client = static::createRestClient();
        $client->request('GET', '/file/');

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseSchemaRel('http://localhost/schema/file/openapi.json', $response);

        $this->assertEquals([], $results);
    }

    /**
     * validate that we can post a new file
     *
     * @return void
     */
    public function testPostAndUpdateFile()
    {
        $fixtureData = file_get_contents(__DIR__.'/fixtures/test.txt');
        $fileHash = hash('sha256', $fixtureData);
        $fileHashCustom = 'some-custom-hash-for-testing';

        $client = static::createRestClient();
        $client->post(
            '/file/',
            $fixtureData,
            [],
            [],
            ['CONTENT_TYPE' => 'text/plain'],
            false
        );
        $this->assertEmpty($client->getResults());
        $response = $client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());

        $fileLocation = $response->headers->get('Location');

        // update file contents to update mod date
        $client = static::createRestClient();
        $client->put(
            $fileLocation,
            $fixtureData,
            [],
            [],
            ['CONTENT_TYPE' => 'text/plain'],
            false
        );
        $this->assertEmpty($client->getResults(), $client->getResponse()->getContent());
        $response = $client->getResponse();
        $this->assertEquals(204, $response->getStatusCode());

        $client = static::createRestClient();
        $client->request('GET', $fileLocation, [], [], ['HTTP_ACCEPT' => 'application/json']);
        $data = $client->getResults();

        // check for valid format
        $this->assertNotFalse(\DateTime::createFromFormat(\DateTime::RFC3339, $data->metadata->createDate));
        $this->assertNotFalse(\DateTime::createFromFormat(\DateTime::RFC3339, $data->metadata->modificationDate));
        // Check valid hash encoding if no hash sent
        $this->assertEquals($fileHash, $data->metadata->hash);

        $data->links = [];
        $link = new \stdClass;
        $link->{'$ref'} = 'http://localhost/core/app/tablet';
        $data->links[] = $link;

        $filename = "test.txt";
        $data->metadata->filename = $filename;
        $data->metadata->hash = $fileHashCustom;

        $client = static::createRestClient();
        $client->put(sprintf('/file/%s', $data->id), $data, [], [], ['CONTENT_TYPE' => 'application/json']);

        // re-fetch
        $client = static::createRestClient();
        $client->request('GET', sprintf('/file/%s', $data->id), [], [], ['HTTP_ACCEPT' => 'application/json']);
        $results = $client->getResults();

        $this->assertEquals($link->{'$ref'}, $results->links[0]->{'$ref'});
        $this->assertEquals($filename, $results->metadata->filename);
        $this->assertEquals($fileHashCustom, $data->metadata->hash);

        $client = static::createRestClient();
        $client->request('GET', sprintf('/file/%s', $data->id), [], [], ['HTTP_ACCEPT' => 'text/plain']);

        $results = $client->getResponse()->getContent();

        $this->assertEquals($fixtureData, $results);

        // change link and add second link
        $data->links[0]->{'$ref'} = 'http://localhost/core/app/admin';
        $link = new \stdClass;
        $link->{'$ref'} = 'http://localhost/core/app/web';
        $data->links[] = $link;

        // also add action command
        $command = new \stdClass();
        $command->command = 'print';
        $data->metadata->action = [$command];

        // also add additionalInformation
        $data->metadata->additionalInformation = 'someInfo';

        $client = static::createRestClient();
        $client->put(sprintf('/file/%s', $data->id), $data, [], [], ['CONTENT_TYPE' => 'application/json']);

        // re-fetch
        $client = static::createRestClient();
        $client->request('GET', sprintf('/file/%s', $data->id), [], [], ['HTTP_ACCEPT' => 'application/json']);
        $results = $client->getResults();

        $this->assertEquals($data->links[0]->{'$ref'}, $results->links[0]->{'$ref'});
        $this->assertEquals($data->links[1]->{'$ref'}, $results->links[1]->{'$ref'});

        // check metadata
        $this->assertEquals(18, $data->metadata->size);
        $this->assertEquals('text/plain', $data->metadata->mime);
        $this->assertEquals('test.txt', $data->metadata->filename);
        $this->assertEquals('print', $data->metadata->action[0]->command);
        $this->assertEquals('someInfo', $data->metadata->additionalInformation);
        $this->assertNotNull($data->metadata->createDate);
        $this->assertNotNull($data->metadata->modificationDate);

        // remove a link
        unset($data->links[1]);

        $client = static::createRestClient();
        $client->put(sprintf('/file/%s', $data->id), $data, [], [], ['CONTENT_TYPE' => 'application/json']);
        // re-fetch
        $client = static::createRestClient();
        $client->request('GET', sprintf('/file/%s', $data->id), [], [], ['HTTP_ACCEPT' => 'application/json']);
        $results = $client->getResults();

        $this->assertEquals($data->links[0]->{'$ref'}, $results->links[0]->{'$ref'});
        $this->assertCount(1, $results->links);

        // remove last link
        $data->links = [];
        $client = static::createRestClient();
        $client->put(sprintf('/file/%s', $data->id), $data, [], [], ['CONTENT_TYPE' => 'application/json']);

        // re-fetch
        $client = static::createRestClient();
        $client->request('GET', sprintf('/file/%s', $data->id), [], [], ['HTTP_ACCEPT' => 'application/json']);

        $results = $client->getResults();

        $this->assertEmpty($results->links);

        // Let's update links but without sending file and still have file info
        $id = $data->id;
        $data = new \stdClass;
        $data->id = $id;
        $link = new \stdClass;
        $link->{'$ref'} = 'http://localhost/core/app/web';
        $data->links = [];
        $data->links[] = $link;
        $client = static::createRestClient();
        $client->put(sprintf('/file/%s', $id), $data, [], [], ['CONTENT_TYPE' => 'application/json']);
        // re-fetch
        $client = static::createRestClient();
        $client->request('GET', sprintf('/file/%s', $data->id), [], [], ['HTTP_ACCEPT' => 'application/json']);
        $data = $client->getResults();
        // check metadata for kept file info
        $this->assertEquals(18, $data->metadata->size);
        $this->assertEquals('text/plain', $data->metadata->mime);
        $this->assertEquals('test.txt', $data->metadata->filename);
        $this->assertNotNull($data->metadata->createDate);
        $this->assertNotNull($data->metadata->modificationDate);
    }

    /**
     * validate that we can post a new file
     *
     * @return void
     */
    public function testPostNewFile()
    {
        $client = static::createRestClient();

        $client->post(
            '/file',
            file_get_contents(__DIR__.'/fixtures/test.txt'),
            [],
            [],
            ['CONTENT_TYPE' => 'text/plain'],
            false
        );

        $response = $client->getResponse();
        $linkHeader = $response->headers->get('Link');

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertMatchesRegularExpression('@/file/[a-z0-9]{44}>; rel="self"@', $linkHeader);
    }

    /**
     * validate that we can put a new file with a custom id
     *
     * @return void
     */
    public function testPutNewFile()
    {
        $client = static::createRestClient();

        $client->put(
            '/file/testPutNewFile',
            file_get_contents(__DIR__ . '/fixtures/test.txt'),
            [],
            [],
            ['CONTENT_TYPE' => 'text/plain'],
            false
        );

        $response = $client->getResponse();
        $linkHeader = $response->headers->get('Link');

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertStringContainsString('file/testPutNewFile>; rel="self"', $linkHeader);
    }

    /**
     * validate that we can delete a file
     *
     * @return void
     */
    public function testDeleteFile()
    {
        $fixtureData = file_get_contents(__DIR__.'/fixtures/test.txt');
        $client = static::createRestClient();
        $client->post(
            '/file/',
            $fixtureData,
            [],
            [],
            ['CONTENT_TYPE' => 'text/plain'],
            false
        );
        $response = $client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());

        // re-fetch
        $client = static::createRestClient();
        $client->request('GET', $response->headers->get('Location'), [], [], ['HTTP_ACCEPT' => 'application/json']);
        $data = $client->getResults();

        $client = static::createRestClient();
        $client->request('DELETE', sprintf('/file/%s', $data->id));

        $client = static::createRestClient();
        $client->request('GET', sprintf('/file/%s', $data->id));

        $response = $client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * validate that we can update the content from a file
     *
     * @return void
     */
    public function testUpdateFileContent()
    {
        $fixtureData = file_get_contents(__DIR__.'/fixtures/test.txt');
        $contentType = 'text/plain';
        $newData = "This is a new text!!!";
        $client = static::createRestClient();
        $client->post(
            '/file/',
            $fixtureData,
            [],
            [],
            ['CONTENT_TYPE' => $contentType],
            false
        );
        $response = $client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());

        $linkHeader = $response->headers->get('Link');
        $this->assertMatchesRegularExpression('@/file/[a-z0-9]{44}>; rel="self"@', $linkHeader);

        // re-fetch
        $client = static::createRestClient();
        $client->request('GET', $response->headers->get('Location'), [], [], ['HTTP_ACCEPT' => 'application/json']);
        $retData = $client->getResults();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(strlen($fixtureData), $retData->metadata->size);
        $this->assertEquals($contentType, $retData->metadata->mime);

        $this->updateFileContent($retData->id, $newData, $contentType);
    }

    /**
     * post a file without any mime type.. check if that mime type is correctly determined.
     * fetch content back and compare the contents of the file
     *
     * @return void
     */
    public function testPostFileContentMimeDetectionAndContent()
    {
        $testData = file_get_contents(__DIR__.'/resources/testpicture.jpg');
        $contentType = 'image/jpeg';
        $client = static::createRestClient();

        $client->post(
            '/file/',
            $testData,
            [],
            [],
            [],
            false
        );
        $response = $client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());

        $linkHeader = $response->headers->get('Link');
        $this->assertMatchesRegularExpression('@/file/[a-z0-9]{44}>; rel="self"@', $linkHeader);

        // re-fetch
        $client = static::createRestClient();
        $client->request('GET', $response->headers->get('Location'), [], [], ['HTTP_ACCEPT' => 'application/json']);
        $retData = $client->getResults();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals($contentType, $retData->metadata->mime);

        $client = static::createRestClient();
        $client->request(
            'GET',
            $response->headers->get('Location'),
            [],
            [],
            ['ACCEPT' => $contentType]
        );

        $response = $client->getInternalResponse();

        $this->assertTrue(($response->getContent() === $testData));
    }

    /**
     * here we PUT a file, then try to update the mimetype in the metadata
     * to 'something/other' and see if we can still GET it and receive the correct mime type
     * (meaning we were not able to modify the mime type)
     *
     * @return void
     */
    public function testIllegalMimeTypeModificationHandling()
    {
        $fileData = "This is a new text!!!";
        $contentType = 'text/plain';

        $client = static::createRestClient();
        $client->put(
            '/file/mimefile',
            $fileData,
            [],
            [],
            [],
            [],
            false
        );
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        // GET the metadata
        $client = static::createRestClient();
        $client->request('GET', '/file/mimefile', [], [], ['HTTP_ACCEPT' => 'application/json']);
        $retData = $client->getResults();

        $this->assertEquals($retData->metadata->mime, $contentType);

        // change metadata and save
        $retData->metadata->mime = 'something/other';

        $client = static::createRestClient();
        $client->put('/file/mimefile', $retData, [], [], ['CONTENT_TYPE' => 'application/json']);

        $client = static::createRestClient();
        $client->request(
            'GET',
            '/file/mimefile',
            [],
            [],
            ['ACCEPT' => '*/*']
        );

        $response = $client->getInternalResponse();

        // still the good one?
        $this->assertStringContainsString($contentType, $response->getHeader('content-type'));

        $client = static::createRestClient();
        $client->request('DELETE', '/file/mimefile');
    }

    /**
     * test multipart request
     *
     * @return void
     */
    public function testMultipartRequest()
    {
        $client = static::createRestClient();

        $content = [
            '--------------------------2f4f7d5be86eaf34',
            'Content-Disposition: form-data; name="metadata"',
            '',
            '{"metadata":{"filename": "argo-logo.png"}}',
            '--------------------------2f4f7d5be86eaf34',
            'Content-Disposition: form-data; name="upload"; filename="logo.png"',
            'Content-Type: image/png',
            '',
            file_get_contents(__DIR__.'/resources/logo.png'),
            '--------------------------2f4f7d5be86eaf34--',
        ];

        $client->post(
            "/file/",
            implode("\r\n", $content),
            [],
            [],
            [
            'CONTENT_TYPE' => 'multipart/form-data; boundary=------------------------2f4f7d5be86eaf34'
            ],
            false
        );

        $response = $client->getResponse();
        $location = $response->headers->get('location');

        $client = static::createRestClient();
        $client->request('GET', $location);

        file_put_contents('/tmp/logo', $client->getResponse()->getContent(false));

        // assert file content
        $this->assertEquals(
            file_get_contents(__DIR__.'/resources/logo.png'),
            $client->getResponse()->getContent(false)
        );
    }

    /**
     * test behavior when data sent was multipart/form-data
     *
     * @return void
     */
    public function testPutNewFileViaForm()
    {
        copy(__DIR__ . '/fixtures/test.txt', sys_get_temp_dir() . '/test.txt');
        $file = sys_get_temp_dir() . '/test.txt';
        $uploadedFile = new UploadedFile($file, 'test.txt', 'text/plain');

        $jsonData = '{
          "id": "myPersonalFile",
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
            "hash": "fix,Not,allowEd,%&ç*2$a-here-demo-test-hash",
            "action":[{"command":"print"},{"command":"archive"}],
            "additionalInformation": "someInfo",
            "additionalProperties": [
                {"name": "testName", "value": "testValue"},
                {"name": "testName2", "value": "testValue2"}
            ],
            "filename": "customFileName"
          }
        }';

        $client = static::createRestClient();
        $client->put(
            '/file/myPersonalFile',
            null,
            [
                'metadata' => $jsonData,
            ],
            [
                'upload' => $uploadedFile,
            ],
            [],
            false
        );

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertNotContains('location', $response->headers->all());

        $response = $this->updateFileContent('myPersonalFile', "This is a new text!!!");

        $metaData = json_decode($jsonData, true);
        $returnData = json_decode($response->getContent(), true);

        $this->assertEquals($metaData['links'], $returnData['links']);
        $this->assertEquals($metaData['metadata']['action'], $returnData['metadata']['action']);
        $this->assertEquals(
            $metaData['metadata']['additionalInformation'],
            $returnData['metadata']['additionalInformation']
        );
        $this->assertEquals(
            $metaData['metadata']['additionalProperties'],
            $returnData['metadata']['additionalProperties']
        );
        $this->assertCount(2, $returnData['metadata']['additionalProperties']);

        // is set by file service!
        $this->assertEquals('test.txt', $returnData['metadata']['filename']);
        $this->assertEquals(
            '2113410fe33761122a00ccaf7bce6b6ac3498b1f0c9dab81ffac503f5293a04a',
            $returnData['metadata']['hash']
        );

        // override metadata!
        $returnData['metadata']['hash'] = 'MY-CUSTOM-HASH';
        $returnData['metadata']['filename'] = 'MY-CUSTOM-FILENAME';

        $client->put(
            '/file/myPersonalFile',
            $returnData
        );

        // get it again!
        $client = static::createRestClient();
        $client->request('GET', '/file/myPersonalFile', [], [], ['HTTP_ACCEPT' => 'application/json']);

        $resp = $client->getResponse();
        $newData = json_decode($resp->getContent(), true);

        $this->assertEquals('MY-CUSTOM-HASH', $newData['metadata']['hash']);
        $this->assertEquals('MY-CUSTOM-FILENAME', $newData['metadata']['filename']);

        // clean up
        $client = $this->createRestClient();
        $client->request(
            'DELETE',
            '/file/myPersonalFile'
        );
    }

    /**
     * test behavior when data sent was multipart/form-data
     *
     * @return void
     */
    public function testPutNewJsonFileViaFormConflictingId()
    {
        $fileId = 'simple-json-content';
        $newContent = '{
          "id": "myPersonalFile",
          "someArrayData": [
            {
              "field1": "fieldDataValue"
            }
          ],
          "UnknownData": {
            "testField": "filed"
          }
        }';

        $client = static::createRestClient();
        $client->put(
            sprintf('/file/%s', $fileId),
            $newContent,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            false
        );

        // conflicting ID!
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        // clean up
        $client = $this->createRestClient();
        $client->request(
            'DELETE',
            '/file/'.$fileId
        );
    }

    /**
     * save json content as other mime type!
     *
     * @return void
     */
    public function testSaveJsonAsFile()
    {
        $fileId = 'simple-json-content';
        $newContent = '{
          "THIS IS JSON": true
        }';

        $client = static::createRestClient();
        $client->put(
            sprintf('/file/%s', $fileId),
            $newContent,
            [],
            [],
            ['CONTENT_TYPE' => 'text/plain'],
            false
        );

        // conflicting ID!
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        $client->request('GET', sprintf('/file/%s', $fileId), [], [], ['HTTP_ACCEPT' => 'text/plain']);

        $this->assertEquals($newContent, (string) $client->getResponse()->getContent());

        // now, get metadata for it
        $client->request('GET', sprintf('/file/%s', $fileId), [], [], ['HTTP_ACCEPT' => 'application/json']);

        $res = $client->getResults();
        $this->assertEquals(42, $res->metadata->size);
        $this->assertEquals("text/plain", $res->metadata->mime);
        $this->assertEquals("simple-json-content", $res->metadata->filename);
    }

    /**
     * Verifies the update of a file content.
     *
     * @param string $fileId      identifier of the file to be updated
     * @param string $newContent  new content to be stored in the file
     * @param string $contentType Content-Type of the file
     *
     * @return null|Response
     */
    private function updateFileContent($fileId, $newContent, $contentType = 'text/plain')
    {
        $client = static::createRestClient();
        $client->put(
            sprintf('/file/%s', $fileId),
            $newContent,
            [],
            [],
            ['CONTENT_TYPE' => $contentType],
            false
        );

        $client = static::createRestClient();
        $client->request('GET', sprintf('/file/%s', $fileId), [], [], ['HTTP_ACCEPT' => 'application/json']);

        $retData = $client->getResults();
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($contentType, $retData->metadata->mime);

        return $response;
    }
}
