<?php
/**
 * SerializerSelectExclusionStrategyTest class file
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use GravitonDyn\TestCaseDeepEqualNamingBundle\DataFixtures\MongoDB\LoadTestCaseDeepEqualNamingData;
use Symfony\Component\HttpFoundation\Response;
use GravitonDyn\TestCasePrimitiveArrayBundle\DataFixtures\MongoDB\LoadTestCasePrimitiveArrayData;
use GravitonDyn\TestCaseNullExtrefBundle\DataFixtures\MongoDB\LoadTestCaseNullExtrefData;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SerializerSelectExclusionStrategyTest extends RestTestCase
{

    /**
     * load fixtures (in this case we can reuse fixtures from other tests)
     *
     * @return void
     */
    public function setUp() : void
    {
        if (!class_exists(LoadTestCasePrimitiveArrayData::class)) {
            $this->markTestSkipped('TestCasePrimitiveArray definition is not loaded');
        }
        if (!class_exists(LoadTestCaseNullExtrefData::class)) {
            $this->markTestSkipped('TestCaseNullExtref definition is not loaded');
        }

        $this->loadFixturesLocal(
            [
                LoadTestCasePrimitiveArrayData::class,
                LoadTestCaseNullExtrefData::class,
                LoadTestCaseDeepEqualNamingData::class
            ]
        );
    }

    /**
     * Test testRqlSelectionOnArrays testing the correct serialization of nested arrays
     *
     * @return void
     */
    public function testRqlSelectionOnArrays()
    {
        $expectedResult = json_decode(
            file_get_contents(dirname(__FILE__).'/../resources/serializer-exclusion-array.json'),
            false
        );

        $client = static::createRestClient();
        $client->request(
            'GET',
            '/testcase/primitivearray/testdata?select(hash.strarray,arrayhash.intarray,arrayhash.hasharray)'
        );
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals($expectedResult, $client->getResults());
    }

    /**
     * Test testRqlSelectionOnNested testing the correct serialization of deeply nested values
     *
     * @return void
     */
    public function testRqlSelectionOnNested()
    {
        $expectedResult = json_decode(
            file_get_contents(dirname(__FILE__).'/../resources/serializer-exclusion-nested.json'),
            false
        );

        $client = static::createRestClient();
        $client->request(
            'GET',
            '/testcase/nullextref/testdata?select(requiredExtref,requiredExtrefDeep.deep.deep,optionalExtrefDeep)'
        );
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        /* expect empty arrays */
        $expectedResult->optionalExtrefArray = [];
        $expectedResult->requiredExtrefArray = [];

        $this->assertEquals($expectedResult, $client->getResults());
    }

    /**
     * Test testRqlSelectionOnNestedDouble testing the correct serialization of deeply nested values
     * The error was that if fields had the same name only first was checked and only the second if first not empty
     *
     * @return void
     */
    public function testRqlSelectionOnNestedDouble()
    {
        $expectedResult = json_decode(
            file_get_contents(dirname(__FILE__).'/../resources/serializer-exclusion-nested-double.json'),
            false
        );

        $client = static::createRestClient();
        $client->request(
            'GET',
            '/testcase/deep-naming/?select(level.levela.levela1,level.levelb.levelb1)'
        );
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertEquals($expectedResult, $client->getResults());
    }
}
