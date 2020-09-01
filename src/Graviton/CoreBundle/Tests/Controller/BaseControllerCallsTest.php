<?php
/**
 * test for custom base controller calls
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestServicesBundle\Service\Random;
use GravitonDyn\TestCaseBaseControllerCallsBundle\DataFixtures\MongoDB\LoadTestCaseBaseControllerCallsData;
use Symfony\Component\HttpFoundation\Response;
use Graviton\TestBundle\Test\RestTestCase;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class BaseControllerCallsTest extends RestTestCase
{
    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->loadFixturesLocal(
            [
                LoadTestCaseBaseControllerCallsData::class
            ]
        );
    }

    /**
     * @return void
     */
    public function testRandomServiceIsCalled()
    {
        $client = static::createRestClient();
        $client->request('GET', '/testcase/basecontroller-calls/?sort(+id)');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $results = $client->getResults();

        $this->assertEquals("one", $results[0]->data);
        $this->assertEquals("two", $results[1]->data);

        $this->assertContains($results[0]->random, Random::getRandomStrings());
        $this->assertContains($results[1]->random, Random::getRandomStrings());
    }
}
