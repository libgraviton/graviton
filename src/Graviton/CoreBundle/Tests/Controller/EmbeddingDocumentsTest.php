<?php
/**
 * EmbeddingDocumentsTest class file
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Graviton\TestBundle\Test\RestTestCase;
use GravitonDyn\EmbedTestEntityBundle\DataFixtures\MongoDB\LoadEmbedTestEntityData;
use GravitonDyn\EmbedTestDocumentAsReferenceBundle\DataFixtures\MongoDB\LoadEmbedTestDocumentAsReferenceData;
use GravitonDyn\EmbedTestDocumentAsEmbeddedBundle\DataFixtures\MongoDB\LoadEmbedTestDocumentAsEmbeddedData;
use GravitonDyn\EmbedTestDocumentAsDeepReferenceBundle\DataFixtures\MongoDB\LoadEmbedTestDocumentAsDeepReferenceData;
use GravitonDyn\EmbedTestDocumentAsDeepEmbeddedBundle\DataFixtures\MongoDB\LoadEmbedTestDocumentAsDeepEmbeddedData;
use GravitonDyn\EmbedTestHashAsEmbeddedBundle\DataFixtures\MongoDB\LoadEmbedTestHashAsEmbeddedData;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class EmbeddingDocumentsTest extends RestTestCase
{
    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp() : void
    {
        if (!class_exists(LoadEmbedTestEntityData::class) ||
            !class_exists(LoadEmbedTestDocumentAsEmbeddedData::class) ||
            !class_exists(LoadEmbedTestDocumentAsReferenceData::class) ||
            !class_exists(LoadEmbedTestHashAsEmbeddedData::class)) {
            $this->markTestSkipped('Test definitions are not loaded');
        }

        $this->loadFixturesLocal(
            [
                LoadEmbedTestEntityData::class,
                LoadEmbedTestDocumentAsEmbeddedData::class,
                LoadEmbedTestDocumentAsReferenceData::class,
                LoadEmbedTestDocumentAsDeepEmbeddedData::class,
                LoadEmbedTestDocumentAsDeepReferenceData::class,
                LoadEmbedTestHashAsEmbeddedData::class,
            ]
        );
    }

    /**
     * @param string $id   ID
     * @param mixed  $data Data
     * @return void
     */
    private function assertEntityExists($id, $data)
    {
        $client = static::createRestClient();
        $client->request('GET', '/testcase/embedtest-entity/'.$id);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertIsObject($client->getResults());

        $this->assertEquals($id, $client->getResults()->id);
        $this->assertEquals($data, $client->getResults()->data);
    }

    /**
     * @param string $id ID
     * @return void
     */
    private function assertEntityNotExists($id)
    {
        $client = static::createRestClient();
        $client->request('GET', '/testcase/embedtest-entity/'.$id);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
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
        $data->document = (object) [
            'id'    => 'two',
            'data'  => 'two',
        ];

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
        $this->assertEntityNotExists('three');

        // check document
        $client = static::createRestClient();
        $client->request('GET', '/testcase/embedtest-document-as-reference/test');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals($original, $client->getResults());

        // update document
        $data = $client->getResults();
        $data->document = (object) [
            'id'    => 'three',
            'data'  => 'three',
        ];

        $client = static::createRestClient();
        $client->put('/testcase/embedtest-document-as-reference/test', $data);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        // check data
        $client = static::createRestClient();
        $client->request('GET', '/testcase/embedtest-document-as-reference/test');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals($data, $client->getResults());

        // check entities again
        // record "one" was not removed. it is incorrect

        /**
         * this is imho the good behavior. a referenced object shall *not* be deleted
         * when it no longer has any references!
         */

        // $this->assertEntityNotExists('one');
        $this->assertEntityExists('one', 'one');
        $this->assertEntityExists('two', 'two');

        // ok. new entity was added
        $this->assertEntityExists('three', 'three');
    }

    /**
     * Test Document as deep embedded
     *
     * @return void
     */
    public function testDocumentAsDeepEmbedded()
    {
        $original = (object) [
            'id'    => 'test',
            'deep'  => (object) [
                'document'  => (object) [
                    'id'    => 'one',
                    'data'  => 'one',
                ],
            ],
        ];

        // check entities
        $this->assertEntityExists('one', 'one');
        $this->assertEntityExists('two', 'two');

        // check document
        $client = static::createRestClient();
        $client->request('GET', '/testcase/embedtest-document-as-deep-embedded/test');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals($original, $client->getResults());

        // update document
        $data = $client->getResults();
        $data->deep->document = (object) [
            'id'    => 'two',
            'data'  => 'two',
        ];

        $client = static::createRestClient();
        $client->put('/testcase/embedtest-document-as-deep-embedded/test', $data);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        // check data
        $client = static::createRestClient();
        $client->request('GET', '/testcase/embedtest-document-as-deep-embedded/test');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals($data, $client->getResults());

        // check entities again
        $this->assertEntityExists('one', 'one');
        $this->assertEntityExists('two', 'two');
    }

    /**
     * Assert that if we send a DELETE request to an entity, referenced objects will not be deleted
     * as well..
     *
     * @return void
     */
    public function testThatReferencedObjectsWillNotBeDeletedOnDelete()
    {
        $client = static::createRestClient();
        $client->request('DELETE', '/testcase/embedtest-document-as-deep-reference/test');
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
        $this->assertEmpty($client->getResults());

        // check it's really gone ;-)
        $client = static::createRestClient();
        $client->request('GET', '/testcase/embedtest-document-as-deep-reference/test');
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        // both entities should still exist
        $this->assertEntityExists('one', 'one');
        $this->assertEntityExists('two', 'two');
    }

    /**
     * Test Document as deep reference
     *
     * @return void
     */
    public function testDocumentAsDeepReference()
    {
        $original = (object) [
            'id'    => 'test',
            'deep'  => (object) [
                'document'  => (object) [
                    'id'    => 'one',
                    'data'  => 'one',
                ],
            ],
        ];

        // check entities
        $this->assertEntityExists('one', 'one');
        $this->assertEntityExists('two', 'two');

        // check document
        $client = static::createRestClient();
        $client->request('GET', '/testcase/embedtest-document-as-deep-reference/test');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals($original, $client->getResults());

        // update document
        $data = $client->getResults();
        $data->deep->document = (object) [
            'id'    => 'three',
            'data'  => 'three',
        ];

        $client = static::createRestClient();
        $client->put('/testcase/embedtest-document-as-deep-reference/test', $data);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        // check data
        $client = static::createRestClient();
        $client->request('GET', '/testcase/embedtest-document-as-deep-reference/test');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals($data, $client->getResults());

        // check entities again
        // record "one" was *not* removed.
        $this->assertEntityExists('one', 'one');
        $this->assertEntityExists('two', 'two');
        $this->assertEntityExists('three', 'three');

        // change to two again
        $data = $client->getResults();
        $data->deep->document = (object) [
            'id'    => 'two',
            'data'  => 'two',
        ];

        $client = static::createRestClient();
        $client->put('/testcase/embedtest-document-as-deep-reference/test', $data);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        $client = static::createRestClient();
        $client->request('GET', '/testcase/embedtest-document-as-deep-reference/test');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals($data, $client->getResults());

        // all still exist
        $this->assertEntityExists('one', 'one');
        $this->assertEntityExists('two', 'two');
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
