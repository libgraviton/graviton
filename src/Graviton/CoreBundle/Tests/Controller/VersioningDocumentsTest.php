<?php
/**
 * Versioning Document Entity class file
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\SchemaBundle\Constraint\VersionFieldConstraint;
use GravitonDyn\TestCaseVersioningEntityBundle\DataFixtures\MongoDB\LoadTestCaseVersioningEntityData;
use Symfony\Component\HttpFoundation\Response;
use Graviton\TestBundle\Test\RestTestCase;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
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
        $this->assertEquals($initialVersion, $response->headers->get(VersionFieldConstraint::HEADER_NAME));


        // Update with correct version
        // Let's change something, version should not be possible
        $original->data = "one";
        $original->version = $initialVersion;

        $client = static::createRestClient();
        $client->put('/testcase/versioning-entity/one', $original);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
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
}
