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
class FileControllerTest extends RestTestCase
{

    /**
     * setup client and load fixtures
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->loadFixturesLocal(
            array(
                'GravitonDyn\FileBundle\DataFixtures\MongoDB\LoadFileData'
            )
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

        $this->assertResponseSchemaRel('http://localhost/schema/file/collection', $response);

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
        $client->request('GET', $fileLocation);
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
        $client->put(sprintf('/file/%s', $data->id), $data);

        // re-fetch
        $client = static::createRestClient();
        $client->request('GET', sprintf('/file/%s', $data->id));
        $results = $client->getResults();

        $this->assertEquals($link->{'$ref'}, $results->links[0]->{'$ref'});
        $this->assertEquals($filename, $results->metadata->filename);
        $this->assertEquals($fileHashCustom, $data->metadata->hash);

        $client = static::createClient();
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
        $client->put(sprintf('/file/%s', $data->id), $data);

        // re-fetch
        $client = static::createRestClient();
        $client->request('GET', sprintf('/file/%s', $data->id));
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
        $client->put(sprintf('/file/%s', $data->id), $data);
        // re-fetch
        $client = static::createRestClient();
        $client->request('GET', sprintf('/file/%s', $data->id));
        $results = $client->getResults();

        $this->assertEquals($data->links[0]->{'$ref'}, $results->links[0]->{'$ref'});
        $this->assertCount(1, $results->links);

        // remove last link
        $data->links = [];
        $client = static::createRestClient();
        $client->put(sprintf('/file/%s', $data->id), $data);

        // re-fetch
        $client = static::createRestClient();
        $client->request('GET', sprintf('/file/%s', $data->id));

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
        $client->put(sprintf('/file/%s', $id), $data);
        // re-fetch
        $client = static::createRestClient();
        $client->request('GET', sprintf('/file/%s', $data->id));
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
        $this->assertRegExp('@/file/[a-z0-9]{32}>; rel="self"@', $linkHeader);
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
        $client->request('GET', $response->headers->get('Location'));
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
        $this->assertRegExp('@/file/[a-z0-9]{32}>; rel="self"@', $linkHeader);

        // re-fetch
        $client = static::createRestClient();
        $client->request('GET', $response->headers->get('Location'));
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
        $this->assertRegExp('@/file/[a-z0-9]{32}>; rel="self"@', $linkHeader);

        // re-fetch
        $client = static::createRestClient();
        $client->request('GET', $response->headers->get('Location'));
        $retData = $client->getResults();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals($contentType, $retData->metadata->mime);

        /** we use the standard client as we don't want to have json forced */
        $client = static::createClient();
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
        $client->request('GET', '/file/mimefile');
        $retData = $client->getResults();

        $this->assertEquals($retData->metadata->mime, $contentType);

        // change metadata and save
        $retData->metadata->mime = 'something/other';

        $client = static::createRestClient();
        $client->put('/file/mimefile', $retData);

        $client = static::createClient();
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
     * test getting collection schema
     *
     * @return void
     */
    public function testGetFileCollectionSchemaInformation()
    {
        $client = static::createRestClient();

        $client->request('GET', '/schema/file/collection');

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType('application/schema+json', $response);
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals('Array of file objects', $results->title);
        $this->assertEquals('array', $results->type);
        $this->assertIsFileSchema($results->items);

        $this->assertCorsHeaders('GET, POST, PUT, PATCH, DELETE, OPTIONS', $response);
        $this->assertContains(
            'Link',
            explode(',', $response->headers->get('Access-Control-Expose-Headers'))
        );

        $linkHeader = LinkHeader::fromString($response->headers->get('Link'));

        $this->assertEquals(
            'http://localhost/schema/file/collection',
            $linkHeader->getRel('self')->getUri()
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
        $this->assertEquals($metaData['metadata']['filename'], $returnData['metadata']['filename']);
        $this->assertEquals('fix-Not-allowEd------2-a-here-demo-test-hash', $returnData['metadata']['hash']);

        // clean up
        $client = $this->createClient();
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
    public function testPutNewFileViaFormHashToLong()
    {
        copy(__DIR__ . '/fixtures/test.txt', sys_get_temp_dir() . '/test.txt');
        $file = sys_get_temp_dir() . '/test.txt';
        $uploadedFile = new UploadedFile($file, 'test.txt', 'text/plain');

        $fixtureData = file_get_contents(__DIR__.'/fixtures/test.txt');
        $correctHash = hash('sha256', $fixtureData);

        // Max 64 length, should not contain the extra bitsasd
        $toLongHashExtra = $correctHash . '-some-extra-bits ';

        $jsonData = '{
          "id": "myPersonalFile2",
          "metadata": {
            "hash": "'.$toLongHashExtra.'",
            "action":[{"command":"archive"}],
            "filename": "customFileName"
          }
        }';

        $client = static::createRestClient();
        $client->put(
            '/file/myPersonalFile2',
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

        $response = $this->updateFileContent('myPersonalFile2', "This is a new text!!!");

        $metaData = json_decode($jsonData, true);
        $returnData = json_decode($response->getContent(), true);

        $this->assertEquals($metaData['metadata']['filename'], $returnData['metadata']['filename']);

        // Should NOT be equal as hash was to long and was chopped.
        $this->assertNotEquals($metaData['metadata']['hash'], $returnData['metadata']['hash']);
        // But it should be equal to to first part of the hash
        $this->assertEquals($correctHash, $returnData['metadata']['hash']);

        // clean up
        $client = $this->createClient();
        $client->request(
            'DELETE',
            '/file/myPersonalFile2'
        );
    }



    /**
    /**
     * test behavior when data sent was multipart/form-data
     *
     * @return void
     */
    public function testPutNewJsonFileViaForm()
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

        $this->updateFileContent($fileId, $newContent);

        $client = $this->createClient([], ['CONTENT_TYPE' => 'text/plain']);
        $client->request('GET', sprintf('/file/%s', $fileId));

        $retData = $client->getResponse()->getContent();

        $this->assertEquals($retData, $newContent);

        // clean up
        $client = $this->createClient();
        $client->request(
            'DELETE',
            '/file/'.$fileId
        );
    }

    /**
     * check if a schema is of the file type
     *
     * @param \stdClass $schema schema from service to validate
     *
     * @return void
     */
    private function assertIsFileSchema(\stdClass $schema)
    {
        $this->assertEquals('File', $schema->title);
        $this->assertEquals('File storage service', $schema->description);
        $this->assertEquals('object', $schema->type);

        $this->assertEquals('string', $schema->properties->id->type);
        $this->assertEquals('ID', $schema->properties->id->title);
        $this->assertEquals('Unique identifier', $schema->properties->id->description);
        $this->assertObjectNotHasAttribute('readOnly', $schema->properties->id);

        // Metadata
        $this->assertEquals('object', $schema->properties->metadata->type);
        $this->assertEquals('Metadata', $schema->properties->metadata->title);
        $this->assertObjectNotHasAttribute('readOnly', $schema->properties->metadata);

        // Metadata size
        $this->assertEquals('integer', $schema->properties->metadata->properties->size->type);
        $this->assertEquals('File size', $schema->properties->metadata->properties->size->title);
        $this->assertEquals('Size of file.', $schema->properties->metadata->properties->size->description);

        // Metadata mime
        $this->assertContains('string', $schema->properties->metadata->properties->mime->type);
        $this->assertEquals('MIME Type', $schema->properties->metadata->properties->mime->title);
        $this->assertEquals('MIME-Type of file.', $schema->properties->metadata->properties->mime->description);

        // Metadata createDate
        $this->assertEquals(['string', 'null'], $schema->properties->metadata->properties->createDate->type);
        $this->assertEquals('date-time', $schema->properties->metadata->properties->createDate->format);
        $this->assertEquals('Creation date', $schema->properties->metadata->properties->createDate->title);
        $this->assertEquals(
            'Timestamp of file upload.',
            $schema->properties->metadata->properties->createDate->description
        );

        // Metadata modificationDate
        $this->assertEquals(['string', 'null'], $schema->properties->metadata->properties->modificationDate->type);
        $this->assertEquals('date-time', $schema->properties->metadata->properties->modificationDate->format);
        $this->assertEquals('Modification date', $schema->properties->metadata->properties->modificationDate->title);
        $this->assertEquals(
            'Timestamp of the last file change.',
            $schema->properties->metadata->properties->modificationDate->description
        );

        // Metadata filename
        $this->assertContains('string', $schema->properties->metadata->properties->filename->type);
        $this->assertEquals('File name', $schema->properties->metadata->properties->filename->title);
        $this->assertEquals(
            'Name of the file as it should get displayed to the user.',
            $schema->properties->metadata->properties->filename->description
        );
        $this->assertObjectNotHasAttribute('readOnly', $schema->properties->metadata->properties->filename);

        // metadata action.command array
        $this->assertContains(
            'string',
            $schema->properties->metadata->properties->action->items->properties->command->type
        );
        $this->assertEquals(
            'Action command array',
            $schema->properties->metadata->properties->action->items->properties->command->title
        );
        $this->assertObjectNotHasAttribute(
            'readOnly',
            $schema->properties->metadata->properties->action->items->properties->command
        );

        // metadata additionalInformation
        $this->assertContains(
            'string',
            $schema->properties->metadata->properties->additionalInformation->type
        );
        $this->assertEquals(
            'Additional Information',
            $schema->properties->metadata->properties->additionalInformation->title
        );
        $this->assertObjectNotHasAttribute(
            'readOnly',
            $schema->properties->metadata->properties->additionalInformation
        );

        // metadata additionalProperties
        $additionalPropertiesSchema = $schema->properties->metadata->properties->additionalProperties;
        $this->assertEquals('array', $additionalPropertiesSchema->type);
        $this->assertEquals('object', $additionalPropertiesSchema->items->type);
        $this->assertEquals('string', $additionalPropertiesSchema->items->properties->name->type);
        $this->assertEquals('property name', $additionalPropertiesSchema->items->properties->name->title);
        $this->assertEquals('string', $additionalPropertiesSchema->items->properties->value->type);
        $this->assertEquals('property value', $additionalPropertiesSchema->items->properties->value->title);

        // Links
        $this->assertEquals('array', $schema->properties->links->type);
        $this->assertEquals('many', $schema->properties->links->format);
        $this->assertEquals('Links', $schema->properties->links->title);
        $this->assertEquals('@todo replace me', $schema->properties->links->description);
        $this->assertObjectNotHasAttribute('readOnly', $schema->properties->links);


        // Links items
        $this->assertEquals('object', $schema->properties->links->items->type);
        $this->assertEquals('Links', $schema->properties->links->items->title);
        $this->assertObjectNotHasAttribute('readOnly', $schema->properties->links->items);


        // Links item type
        $this->assertContains('string', $schema->properties->links->items->properties->type->type);
        $this->assertEquals('Type', $schema->properties->links->items->properties->type->title);
        $this->assertEquals('Type of the link.', $schema->properties->links->items->properties->type->description);
        $this->assertObjectNotHasAttribute('readOnly', $schema->properties->links->items->properties->type);

        // Links item $ref
        $this->assertEquals(['string', 'null'], $schema->properties->links->items->properties->{'$ref'}->type);
        $this->assertEquals('extref', $schema->properties->links->items->properties->{'$ref'}->format);
        $this->assertEquals('Link', $schema->properties->links->items->properties->{'$ref'}->title);
        $this->assertEquals(
            'Link to any document.',
            $schema->properties->links->items->properties->{'$ref'}->description
        );
        $this->assertEquals(
            ['*'],
            $schema->properties->links->items->properties->{'$ref'}->{'x-collection'}
        );

        $this->assertEquals(
            [
                'document.file.file.update',
                'document.file.file.create',
                'document.file.file.delete'
            ],
            $schema->{'x-events'}
        );

        $this->assertObjectNotHasAttribute('readOnly', $schema->properties->links->items->properties->{'$ref'});
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
        $client->request('GET', sprintf('/file/%s', $fileId));

        $retData = $client->getResults();
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($contentType, $retData->metadata->mime);

        return $response;
    }
}
