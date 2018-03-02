<?php
/**
 * Versioning Document Entity class file
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\SchemaBundle\Constraint\VersionServiceConstraint;
use GravitonDyn\TestCaseVersioningEntityBundle\DataFixtures\MongoDB\LoadTestCaseVersioningEntityData;
use Symfony\Component\HttpFoundation\Response;
use Graviton\TestBundle\Test\RestTestCase;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class VersioningDocumentsTest extends RestTestCase
{
    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp()
    {

        if (!class_exists(LoadTestCaseVersioningEntityData::class)) {
            $this->markTestSkipped('Test definitions are not loaded');
        }

        $this->loadFixtures(
            [
                LoadTestCaseVersioningEntityData::class
            ],
            null,
            'doctrine_mongodb'
        );
    }

    /**
     * Test Document as embedded
     *
     * @return void
     */
    public function testPut()
    {
        // check document
        $client = static::createRestClient();
        $client->request('GET', '/testcase/versioning-entity/one');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $original = $client->getResults();
        $this->assertObjectHasAttribute('version', $original, 'Response have no version: '.json_encode($original));

        $initialVersion = $original->version;

        // Let's change something
        $original->data = "one-one";

        $client = static::createRestClient();
        $client->put('/testcase/versioning-entity/one', $original);
        $respo = $client->getResponse();
        $this->assertEquals(Response::HTTP_NO_CONTENT, $respo->getStatusCode(), $respo->getContent());

        // Version has been updated with put
        $initialVersion++;

        $client->request('GET', '/testcase/versioning-entity/one');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $originalB = $client->getResults();
        $this->assertEquals("one-one", $originalB->data, json_encode($originalB));
        $this->assertEquals($initialVersion, $originalB->version, json_encode($originalB));

        // Let's change something, version should not be possible
        $original->data = "one";
        $original->version = 1;

        $client = static::createRestClient();
        $client->put('/testcase/versioning-entity/one', $original);
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals($initialVersion, $response->headers->get(VersionServiceConstraint::HEADER_NAME));


        // Update with correct version
        // Let's change something, version should not be possible
        $original->data = "one";
        $original->version = $initialVersion;

        $client = static::createRestClient();
        $client->put('/testcase/versioning-entity/one', $original);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
    }

    /**
     * there was a bug that clients could 'reset' the version by just not sending it.
     * that's because version is not a required field per se; so the _field_ validator would not execute.
     * this is testing the service validator now.
     *
     * @return void
     */
    public function testSubsequentCreateWithNoVersion()
    {
        // create a record, specify no version
        $record = (object) [
            'id' => 'dude',
            'data' => 'mydata'
        ];

        $client = static::createRestClient();
        $client->put('/testcase/versioning-entity/dude', $record);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        // now again with empty id
        $client = static::createRestClient();
        $client->put('/testcase/versioning-entity/dude', $record);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $client->getResponse()->headers->get(VersionServiceConstraint::HEADER_NAME));
    }

    /**
     * Test Document as embedded
     *
     * @return void
     */
    public function testPost()
    {
        $new = new \stdClass();
        $new->data = 'something-to-test';

        $client = static::createRestClient();
        $client->post('/testcase/versioning-entity/', $new);
        $resp = $client->getResponse();
        $this->assertEquals(Response::HTTP_CREATED, $resp->getStatusCode(), $resp->getContent());
        $urlNew = $resp->headers->get('Location');

        // check document
        $client = static::createRestClient();
        $client->request('GET', $urlNew);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $original = $client->getResults();

        $this->assertEquals($new->data, $original->data);
        $this->assertEquals(1, $original->version);
    }



    /**
     * Test Document as embedded
     *
     * @return void
     */
    public function testPatching()
    {
        // create a record, specify no version
        $record = (object) [
            'id' => 'patch-id-test',
            'data' => 'mydata'
        ];

        $client = static::createRestClient();
        $client->put('/testcase/versioning-entity/'.($record->id), $record);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        // PATCH to fail, no version field
        $client = static::createRestClient();
        $patch = json_encode(
            [
                [
                    'op' => 'replace',
                    'path' => '/data',
                    'value' => 'fail here'
                ]
            ]
        );
        $client->request('PATCH', '/testcase/versioning-entity/' . ($record->id), [], [], [], $patch);
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        // PATCH to fail, wrong version number
        $client = static::createRestClient();
        $patch = json_encode(
            [
                [
                    'op' => 'replace',
                    'path' => '/data',
                    'value' => 'should be KO'
                ],
                [
                    'op' => 'replace',
                    'path' => '/version',
                    'value' => 7
                ]
            ]
        );
        $client->request('PATCH', '/testcase/versioning-entity/' . ($record->id), [], [], [], $patch);
        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(1, $response->headers->get(VersionServiceConstraint::HEADER_NAME));

        // PATCH, checking header from failed and use it to patch version.
        $patch = json_encode(
            [
                [
                    'op' => 'replace',
                    'path' => '/data',
                    'value' => 'should be OK'
                ],
                [
                    'op' => 'replace',
                    'path' => '/version',
                    'value' => $response->headers->get(VersionServiceConstraint::HEADER_NAME)
                ]
            ]
        );

        $client->request('PATCH', '/testcase/versioning-entity/' . ($record->id), [], [], [], $patch);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        // Let's check that the patch updated counter version.
        $client->request('GET', '/testcase/versioning-entity/' . ($record->id));
        $response = $client->getResponse();
        $current = json_decode($response->getContent());
        $this->assertEquals(2, $current->version);
        $this->assertEquals('should be OK', $current->data);
    }
}
