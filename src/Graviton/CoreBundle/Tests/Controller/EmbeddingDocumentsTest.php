<?php
/**
 * EmbeddingDocumentsTest class file
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Graviton\TestBundle\Test\RestTestCase;
Use GravitonDyn\EmbedTestEntityBundle\DataFixtures\MongoDB\LoadEmbedTestEntityData;
use GravitonDyn\EmbedTestDocumentAsReferenceBundle\DataFixtures\MongoDB\LoadEmbedTestDocumentAsReferenceData;
use GravitonDyn\EmbedTestDocumentAsEmbeddedBundle\DataFixtures\MongoDB\LoadEmbedTestDocumentAsEmbeddedData;
use GravitonDyn\EmbedTestHashAsEmbeddedBundle\DataFixtures\MongoDB\LoadEmbedTestHashAsEmbeddedData;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class EmbeddingDocumentsTest extends RestTestCase
{
    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp()
    {
        if (!class_exists(LoadEmbedTestEntityData::class) ||
            !class_exists(LoadEmbedTestDocumentAsEmbeddedData::class) ||
            !class_exists(LoadEmbedTestDocumentAsReferenceData::class) ||
            !class_exists(LoadEmbedTestHashAsEmbeddedData::class)) {
            $this->markTestSkipped('Test definitions are not loaded');
        }

        $this->loadFixtures(
            [
                LoadEmbedTestEntityData::class,
                LoadEmbedTestDocumentAsEmbeddedData::class,
                LoadEmbedTestDocumentAsReferenceData::class,
                LoadEmbedTestHashAsEmbeddedData::class,
            ],
            null,
            'doctrine_mongodb'
        );
    }

    /**
     * @param string $id   ID
     * @param mixed  $data Data
     * @return void
     * @throws \PHPUnit_Framework_AssertionFailedError
     */
    private function assertEntityExists($id, $data)
    {
        $client = static::createRestClient();
        $client->request('GET', '/testcase/embedtest-entity/'.$id);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertInternalType('object', $client->getResults());

        $this->assertEquals($id, $client->getResults()->id);
        $this->assertEquals($data, $client->getResults()->data);
    }

    /**
     * Test Document as embedded
     *
     * @return void
     */
    public function testDocumentAsEmbedded()
    {
        $original = (object) [
            'id' => 'test',
            'document' => (object) ['id' => 'one', 'data' => 'one'],
        ];

        // check entities
        $this->assertEntityExists('one', 'one');
        $this->assertEntityExists('two', 'two');

        // check document
        $client = static::createRestClient();
        $client->request('GET', '/testcase/embedtest-document-as-embedded/test');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals($original, $client->getResults());

        // update document
        $data = $client->getResults();
        $data->document = (object) ['id' => 'two', 'data' => 'two'];

        $client = static::createRestClient();
        $client->put('/testcase/embedtest-document-as-embedded/test', $data);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        // check data
        $client = static::createRestClient();
        $client->request('GET', '/testcase/embedtest-document-as-embedded/test');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals($data, $client->getResults());

        // check entities again
        $this->assertEntityExists('one', 'one');
        $this->assertEntityExists('two', 'two');
    }

    /**
     * Test Document as reference
     *
     * @return void
     */
    public function testDocumentAsReference()
    {
        $original = (object) [
            'id' => 'test',
            'document' => (object) ['id' => 'one', 'data' => 'one'],
        ];

        // check entities
        $this->assertEntityExists('one', 'one');
        $this->assertEntityExists('two', 'two');

        // check document
        $client = static::createRestClient();
        $client->request('GET', '/testcase/embedtest-document-as-reference/test');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals($original, $client->getResults());

        // update document
        $data = $client->getResults();
        $data->document = (object) ['id' => 'three', 'data' => 'three'];

        $client = static::createRestClient();
        $client->put('/testcase/embedtest-document-as-reference/test', $data);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        // check data
        $client = static::createRestClient();
        $client->request('GET', '/testcase/embedtest-document-as-reference/test');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals($data, $client->getResults());

        // check entities again
        $this->assertEntityExists('one', 'one');
        $this->assertEntityExists('two', 'two');

        // ok. new entity was added
        $this->assertEntityExists('three', 'three');
    }

    /**
     * Test Hash as embedded
     *
     * @return void
     */
    public function testHashAsEmbedded()
    {
        $original = (object) [
            'id' => 'test',
            'document' => (object) ['id' => 'one', 'data' => 'one'],
            'documents' => [
                (object) ['id' => 'one', 'data' => 'one'],
                (object) ['id' => 'two', 'data' => 'two'],
            ],
        ];

        // check entities
        $this->assertEntityExists('one', 'one');
        $this->assertEntityExists('two', 'two');

        // check document
        $client = static::createRestClient();
        $client->request('GET', '/testcase/embedtest-hash-as-embedded/test');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals($original, $client->getResults());



        // update document
        $data = $client->getResults();
        $data->document = (object) ['id' => 'two', 'data' => 'two'];
        $data->documents = [
            (object) ['id' => 'three', 'data' => 'three'],
        ];

        $client = static::createRestClient();
        $client->put('/testcase/embedtest-hash-as-embedded/test', $data);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        // check data
        $client = static::createRestClient();
        $client->request('GET', '/testcase/embedtest-hash-as-embedded/test');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals($data, $client->getResults());

        // check entities again
        $this->assertEntityExists('one', 'one');
        $this->assertEntityExists('two', 'two');



        // update document with empty embed-many
        $data = $client->getResults();
        $data->document = (object) ['id' => 'two', 'data' => 'two'];
        $data->documents = [];

        $client = static::createRestClient();
        $client->put('/testcase/embedtest-hash-as-embedded/test', $data);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        // check data
        $client = static::createRestClient();
        $client->request('GET', '/testcase/embedtest-hash-as-embedded/test');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals($data, $client->getResults());

        // check entities again
        $this->assertEntityExists('one', 'one');
        $this->assertEntityExists('two', 'two');
    }
}
