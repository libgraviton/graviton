<?php
/**
 * EmptyObjectControllerTest class file
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class EmptyObjectControllerTest extends RestTestCase
{
    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp()
    {
        if (!class_exists('GravitonDyn\TestCaseEmptyObjectBundle\DataFixtures\MongoDB\LoadTestCaseEmptyObjectData')) {
            $this->markTestSkipped('TestCaseEmptyObject definition is not loaded');
        }

        $this->loadFixtures(
            ['GravitonDyn\TestCaseEmptyObjectBundle\DataFixtures\MongoDB\LoadTestCaseEmptyObjectData'],
            null,
            'doctrine_mongodb'
        );
    }

    /**
     * Test GET one method
     *
     * @param string $id ID
     * @return void
     * @dataProvider dataCheckGetOne
     */
    public function testCheckGetOne($id)
    {
        $client = static::createRestClient();
        $client->request('GET', '/testcase/emptyobject/?id='.$id);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertInternalType('array', $client->getResults());
        $this->assertCount(1, $client->getResults());
    }

    /**
     * Data for GET one test
     *
     * @return array
     */
    public function dataCheckGetOne()
    {
        return [
            'empty all' => [
                'emptyAll',
            ],
            'empty hash' => [
                'emptyHash',
            ],
            'empty unstructured' => [
                'emptyUnstructured',
            ],
            'no empty objects' => [
                'noEmpty',
            ],
        ];
    }

    /**
     * Test GET all method
     *
     * @return void
     */
    public function testCheckGetAll()
    {
        $client = static::createRestClient();
        $client->request('GET', '/testcase/emptyobject/');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertInternalType('array', $client->getResults());
        $this->assertCount(4, $client->getResults());
    }
}
