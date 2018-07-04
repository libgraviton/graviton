<?php
/**
 * EmptyObjectControllerTest class file
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use GravitonDyn\TestCaseIdReadOnlyBundle\Document\TestCaseIdReadOnly;
use GravitonDyn\TestCaseIdReadOnlyBundle\Document\TestCaseIdReadOnlyObjectEmbedded;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ReadOnlyIdControllerTest extends RestTestCase
{
    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp()
    {
        $object = new TestCaseIdReadOnly();
        $object = new TestCaseIdReadOnlyObjectEmbedded();
    }

    /**
     * Test create
     *
     * @return void
     */
    public function testCreateNewElements()
    {
        $main = $this->buildObject('ok');

        $client = static::createRestClient();
        // Should it exists
        $client->request('DELETE', '/testcase/readonlyid/'.$main->id);

        $client->put('/testcase/readonlyid/'.$main->id, $main);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        $client = static::createRestClient();
        $client->request('GET', '/testcase/readonlyid/'.$main->id);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $result = $client->getResults();

        $this->assertTrue($result == $main);
    }

    /**
     * Test create
     *
     * @return void
     */
    public function testCreateNewElementsWithNoId()
    {
        $main = $this->buildObject('bad');
        // Unset a ID
        unset($main->object->id);

        $client = static::createRestClient();
        // Should it exists
        $client->request('DELETE', '/testcase/readonlyid/'.$main->id);

        $client->put('/testcase/readonlyid/'.$main->id, $main);
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        // [{"propertyPath":"object.id","message":"The property id is required"}]
        $result = $client->getResults();
        $this->assertEquals('object.id', $result[0]->propertyPath);
        $this->assertEquals('The property id is required', $result[0]->message);
    }

    /**
     * Simple build of stdClass for testing
     *
     * @param string $pre Id string field for testing
     * @return \stdClass
     */
    private function buildObject($pre)
    {
        $id = $pre.'read-required-id-main';
        $main = new \stdClass();
        $main->id = $id;
        $main->name = 'name_for_'.$id;

        $id = 'read-required-id-l1-'.$id;
        $levelA = new \stdClass();
        $levelA->id = $id;
        $levelA->name = 'name_level1_for_'.$id;

        $id = 'read-required-id-l2-'.$id;
        $levelB = new \stdClass();
        $levelB->id = $id;
        $levelB->name = 'name_level1_for_'.$id;

        $levelA->object = $levelB;
        $main->object = $levelA;

        return $main;
    }
}
