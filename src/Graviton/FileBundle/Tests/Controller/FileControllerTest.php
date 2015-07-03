<?php
/**
 * functional test for /file
 */

namespace Graviton\FileBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;

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
        $client->request('GET', '/file');

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
            '/file',
            $fixtureData,
            [],
            [],
            ['CONTENT_TYPE' => 'text/plain'],
            false
        );
        $data = $client->getResults();
        $response = $client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());

        $data->links = [];
        $link = new \stdClass;
        $link->{'$ref'} = 'http://localhost/core/app/tablet';
        $data->links[] = $link;

        $filename = "test.txt";
        $data->metadata->filename = $filename;

        $client = static::createRestClient();
        $client->put(sprintf('/file/%s', $data->id), $data);

        $results = $client->getResults();

        $this->assertEquals($link->{'$ref'}, $results->links[0]->{'$ref'});
        $this->assertEquals($filename, $results->metadata->filename);

        $client = static::createClient();
        $client->request('GET', sprintf('/file/%s', $data->id), [], [], ['HTTP_ACCEPT' => 'text/plain']);

        $results = $client->getResponse()->getContent();

        $this->assertEquals($fixtureData, $results);

        $data->links[0]->{'$ref'} = 'http://localhost/core/app/admin';

        $client = static::createRestClient();
        $client->put(sprintf('/file/%s', $data->id), $data);
        $results = $client->getResults();

        $this->assertEquals($data->links[0]->{'$ref'}, $results->links[0]->{'$ref'});

        $data->links = [];
        $client = static::createRestClient();
        $client->put(sprintf('/file/%s', $data->id), $data);
        $results = $client->getResults();

        $this->assertEmpty($results->links);

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

        $this->assertEquals(201, $response->getStatusCode());
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
            '/file',
            $fixtureData,
            [],
            [],
            ['CONTENT_TYPE' => 'text/plain'],
            false
        );
        $data = $client->getResults();
        $response = $client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());

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
            '/file',
            $fixtureData,
            [],
            [],
            ['CONTENT_TYPE' => $contentType],
            false
        );
        $retData = $client->getResults();
        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        $this->assertEquals(strlen($fixtureData), $retData->metadata->size);
        $this->assertEquals($contentType, $retData->metadata->mime);

        $client = static::createRestClient();
        $client->put(
            sprintf('/file/%s', $retData->id),
            $newData,
            [],
            [],
            ['CONTENT_TYPE' => $contentType],
            false
        );
        $retData = $client->getResults();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(strlen($newData), $retData->metadata->size);
        $this->assertEquals($contentType, $retData->metadata->mime);
    }
}
