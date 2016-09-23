<?php
/**
 * PrimitiveArrayControllerTest class file
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;
use GravitonDyn\TestCasePrimitiveArrayBundle\DataFixtures\MongoDB\LoadTestCasePrimitiveArrayData;
use GravitonDyn\TestCaseNullExtrefBundle\DataFixtures\MongoDB\LoadTestCaseNullExtrefData;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class SerializerSelectExclusionStrategyTest extends RestTestCase
{
    const DATE_FORMAT = 'Y-m-d\\TH:i:sO';

    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp()
    {
        if (!class_exists(LoadTestCasePrimitiveArrayData::class)) {
            $this->markTestSkipped('TestCasePrimitiveArray definition is not loaded');
        }
        if (!class_exists(LoadTestCaseNullExtrefData::class)) {
            $this->markTestSkipped('TestCaseNullExtref definition is not loaded');
        }

        $this->loadFixtures(
            [LoadTestCasePrimitiveArrayData::class,LoadTestCaseNullExtrefData::class],
            null,
            'doctrine_mongodb'
        );
    }

    /**
     * Test item schema
     *
     * @return void
     */
    public function testRqlSelectionOnArrays()
    {
        $client = static::createRestClient();
        $client->request('GET',
            '/testcase/primitivearray/testdata?select(arrayhash.datearray,arrayhash.intarray,arrayhash.hasharray)');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    public function testRqlSelectionOnNested()
    {
        $client = static::createRestClient();
        $client->request('GET',
            '/testcase/nullextref/testdata?select(requiredExtref,requiredExtrefDeep.deep.deep,optionalExtrefDeep)');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

}
