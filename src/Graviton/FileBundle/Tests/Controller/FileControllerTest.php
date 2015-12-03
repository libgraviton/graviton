<?php
/**
 * functional test for /file
 */

namespace Graviton\FileBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * Basic functional test for /file
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class FileControllerTest extends RestTestCase
{
    /**
     * @const complete content type string expected on a resouce
     */
    const CONTENT_TYPE = 'application/json; charset=UTF-8; profile=http://localhost/schema/file/item';

    /**
     * @const corresponding vendorized schema mime type
     */
    const COLLECTION_TYPE = 'application/json; charset=UTF-8; profile=http://localhost/schema/file/collection';

    /**
     * setup client and load fixtures
     *
     * @return void
     */
    public function setUp()
    {
        $this->loadFixtures(
            array(
                'GravitonDyn\FileBundle\DataFixtures\MongoDB\LoadFileData'
            ),
            null,
            'doctrine_mongodb'
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
        $this->loadFixtures(array(), null, 'doctrine_mongodb');
        $client = static::createRestClient();
        $client->request('GET', '/file/');

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::COLLECTION_TYPE, $response);

        $this->assertEquals(array(), $results);
    }

    /**
     * validate that we can post a new file
     *
     * @return void
     */
    public function testPostAndUpdateFile()
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
        $this->assertEmpty($client->getResults());
        $response = $client->getResponse();
        $this->assertEquals(204, $response->getStatusCode());

        $client = static::createRestClient();
        $client->request('GET', $fileLocation);
        $data = $client->getResults();

        // check for valid format
        $this->assertNotFalse(\DateTime::createFromFormat(\DateTime::RFC3339, $data->metadata->createDate));
        $this->assertNotFalse(\DateTime::createFromFormat(\DateTime::RFC3339, $data->metadata->modificationDate));

        $data->links = [];
        $link = new \stdClass;
        $link->{'$ref'} = 'http://localhost/core/app/tablet';
        $data->links[] = $link;

        $filename = "test.txt";
        $data->metadata->filename = $filename;

        $client = static::createRestClient();
        $client->put(sprintf('/file/%s', $data->id), $data);

        // re-fetch
        $client = static::createRestClient();
        $client->request('GET', sprintf('/file/%s', $data->id));
        $results = $client->getResults();

        $this->assertEquals($link->{'$ref'}, $results->links[0]->{'$ref'});
        $this->assertEquals($filename, $results->metadata->filename);

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

        // read only fields
        $data->metadata->size = 1;
        $data->metadata->createDate = '1984-05-02T00:00:00+0000';
        $data->metadata->modificationDate = '1984-05-02T00:00:00+0000';
        $data->metadata->mime = 'application/octet-stream';
        $client = static::createRestClient();
        $client->put(sprintf('/file/%s', $data->id), $data);

        $expectedErrors = [];
        $expectedError = new \stdClass();
        $expectedError->propertyPath = "data.metadata.size";
        $expectedError->message = "The value \"data.metadata.size\" is read only.";
        $expectedErrors[] = $expectedError;
        $expectedError = new \stdClass();
        $expectedError->propertyPath = "data.metadata.mime";
        $expectedError->message = "The value \"data.metadata.mime\" is read only.";
        $expectedErrors[] = $expectedError;
        $expectedError = new \stdClass();
        $expectedError->propertyPath = "data.metadata.createDate";
        $expectedError->message = "The value \"data.metadata.createDate\" is read only.";
        $expectedErrors[] = $expectedError;
        $expectedError = new \stdClass();
        $expectedError->propertyPath = "data.metadata.modificationDate";
        $expectedError->message = "The value \"data.metadata.modificationDate\" is read only.";
        $expectedErrors[] = $expectedError;

        $this->assertEquals($expectedErrors, $client->getResults());
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

        $this->assertContains(
            '<http://localhost/schema/file/collection>; rel="self"',
            explode(',', $response->headers->get('Link'))
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
        $uploadedFile = new UploadedFile($file, 'test.txt', 'text/plain', 15);

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
            "action":[{"command":"print"},{"command":"archive"}]
          }
        }';

        $client = static::createRestClient();
        $client->put(
            '/file/myPersonalFile',
            [],
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

        // clean up
        $client = $this->createClient();
        $client->request(
            'DELETE',
            $response->headers->get('location')
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
        $this->assertEquals(true, $schema->properties->metadata->properties->size->readOnly);

        // Metadata mime
        $this->assertEquals('string', $schema->properties->metadata->properties->mime->type);
        $this->assertEquals('MIME Type', $schema->properties->metadata->properties->mime->title);
        $this->assertEquals('MIME-Type of file.', $schema->properties->metadata->properties->mime->description);
        $this->assertEquals(true, $schema->properties->metadata->properties->mime->readOnly);

        // Metadata createDate
        $this->assertEquals('string', $schema->properties->metadata->properties->createDate->type);
        $this->assertEquals('date', $schema->properties->metadata->properties->createDate->format);
        $this->assertEquals('Creation date', $schema->properties->metadata->properties->createDate->title);
        $this->assertEquals(
            'Timestamp of file upload.',
            $schema->properties->metadata->properties->createDate->description
        );
        $this->assertEquals(true, $schema->properties->metadata->properties->createDate->readOnly);

        // Metadata modificationDate
        $this->assertEquals('string', $schema->properties->metadata->properties->modificationDate->type);
        $this->assertEquals('date', $schema->properties->metadata->properties->modificationDate->format);
        $this->assertEquals('Modification date', $schema->properties->metadata->properties->modificationDate->title);
        $this->assertEquals(
            'Timestamp of the last file change.',
            $schema->properties->metadata->properties->modificationDate->description
        );
        $this->assertEquals(true, $schema->properties->metadata->properties->modificationDate->readOnly);

        // Metadata filename
        $this->assertEquals('string', $schema->properties->metadata->properties->filename->type);
        $this->assertEquals('File name', $schema->properties->metadata->properties->filename->title);
        $this->assertEquals(
            'Name of the file as it should get displayed to the user.',
            $schema->properties->metadata->properties->filename->description
        );
        $this->assertObjectNotHasAttribute('readOnly', $schema->properties->metadata->properties->filename);

        // metadata action.command array
        $this->assertEquals(
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

        // Links
        $this->assertEquals('array', $schema->properties->links->type);
        $this->assertEquals('many', $schema->properties->links->format);
        $this->assertEquals('links', $schema->properties->links->title);
        $this->assertEquals('@todo replace me', $schema->properties->links->description);
        $this->assertObjectNotHasAttribute('readOnly', $schema->properties->links);


        // Links items
        $this->assertEquals('object', $schema->properties->links->items->type);
        $this->assertEquals('Links', $schema->properties->links->items->title);
        $this->assertObjectNotHasAttribute('readOnly', $schema->properties->links->items);


        // Links item type
        $this->assertEquals('string', $schema->properties->links->items->properties->type->type);
        $this->assertEquals('Type', $schema->properties->links->items->properties->type->title);
        $this->assertEquals('Type of the link.', $schema->properties->links->items->properties->type->description);
        $this->assertObjectNotHasAttribute('readOnly', $schema->properties->links->items->properties->type);

        // Links item $ref
        $this->assertEquals('string', $schema->properties->links->items->properties->{'$ref'}->type);
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
        $this->assertEquals(strlen($newContent), $retData->metadata->size);
        $this->assertEquals($contentType, $retData->metadata->mime);

        return $response;
    }
}
