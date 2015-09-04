<?php
/**
 * RequiredHashControllerTest class file
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class RequiredHashControllerTest extends RestTestCase
{
    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp()
    {
        if (!class_exists('GravitonDyn\TestCaseRequiredHashBundle\DataFixtures\MongoDB\LoadTestCaseRequiredHashData')) {
            $this->markTestSkipped('TestCaseRequiredHashData definition is not loaded');
        }

        $this->loadFixtures(
            ['GravitonDyn\TestCaseRequiredHashBundle\DataFixtures\MongoDB\LoadTestCaseRequiredHashData'],
            null,
            'doctrine_mongodb'
        );
    }

    /**
     * Test POST method with optional hash
     *
     * @return void
     */
    public function testPostWithOptionalHash()
    {
        $data = [
            'name'         => __METHOD__,
            'optionalHash' => [
                'name'     => 'abc',
                'value'    => 123,
                'optional' => '2015-09-03T12:00:00+0000',

                'optionalSubHash' => [
                    'name'     => 'abc',
                    'value'    => 123,
                    'optional' => '2015-09-03T12:00:00+0000',
                ],
                'requiredSubHash' => [
                    'name'     => 'abc',
                    'value'    => 123,
                    'optional' => '2015-09-03T12:00:00+0000',
                ],
            ],
            'requiredHash' => [
                'name'     => 'abc',
                'value'    => 123,
                'optional' => '2015-09-03T12:00:00+0000',

                'optionalSubHash' => [
                    'name'     => 'abc',
                    'value'    => 123,
                    'optional' => '2015-09-03T12:00:00+0000',
                ],
                'requiredSubHash' => [
                    'name'     => 'abc',
                    'value'    => 123,
                    'optional' => '2015-09-03T12:00:00+0000',
                ],
            ],
        ];

        $client = static::createRestClient();
        $client->post('/testcase/requiredHash/', $data);
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertEmpty($client->getResults());
    }

    /**
     * Test POST method without optional hash
     *
     * @return void
     */
    public function testPostWithoutOptionalHash()
    {
        $data = [
            'name'         => __METHOD__,
            'requiredHash' => [
                'name'     => 'abc',
                'value'    => 123,
                'optional' => '2015-09-03T12:00:00+0000',

                'requiredSubHash' => [
                    'name'     => 'abc',
                    'value'    => 123,
                    'optional' => '2015-09-03T12:00:00+0000',
                ],
            ],
        ];

        $client = static::createRestClient();
        $client->post('/testcase/requiredHash/', $data);
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertEmpty($client->getResults());
    }

    /**
     * Test POST method without field in optional hash
     *
     * @return void
     */
    public function testPostWithoutFieldInOptionalHash()
    {
        $data = [
            'name'         => __METHOD__,
            'optionalHash' => [
                'name'     => 'abc',
            ],
            'requiredHash' => [
                'name'     => 'abc',
                'value'    => 123,
                'optional' => '2015-09-03T12:00:00+0000',

                'optionalSubHash' => [
                    'name'     => 'abc',
                ],
                'requiredSubHash' => [
                    'name'     => 'abc',
                    'value'    => 123,
                    'optional' => '2015-09-03T12:00:00+0000',
                ],
            ],
        ];

        $client = static::createRestClient();
        $client->post('/testcase/requiredHash/', $data);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            [
                (object) [
                    'propertyPath'  => 'data.optionalHash.value',
                    'message'       => 'This value should not be blank.',
                ],
                (object) [
                    'propertyPath'  => 'data.optionalHash.requiredSubHash.name',
                    'message'       => 'This value should not be blank.',
                ],
                (object) [
                    'propertyPath'  => 'data.optionalHash.requiredSubHash.value',
                    'message'       => 'This value should not be blank.',
                ],
                (object) [
                    'propertyPath'  => 'data.requiredHash.optionalSubHash.value',
                    'message'       => 'This value should not be blank.',
                ],
            ],
            $client->getResults()
        );
    }

    /**
     * Test POST method without required hash
     *
     * @return void
     */
    public function testPostWithoutRequiredHash()
    {
        $data = [
            'name' => __METHOD__,
        ];

        $client = static::createRestClient();
        $client->post('/testcase/requiredHash/', $data);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            [
                (object) [
                    'propertyPath'  => 'data.requiredHash.name',
                    'message'       => 'This value should not be blank.',
                ],
                (object) [
                    'propertyPath'  => 'data.requiredHash.value',
                    'message'       => 'This value should not be blank.',
                ],
                (object) [
                    'propertyPath'  => 'data.requiredHash.requiredSubHash.name',
                    'message'       => 'This value should not be blank.',
                ],
                (object) [
                    'propertyPath'  => 'data.requiredHash.requiredSubHash.value',
                    'message'       => 'This value should not be blank.',
                ],
            ],
            $client->getResults()
        );
    }

    /**
     * Test POST method with empty optional hash
     *
     * @return void
     */
    public function testPostWithEmptyOptionalHash()
    {
        $data = [
            'name'         => __METHOD__,
            'optionalHash' => [
                'name'     => null,
                'value'    => null,
                'optional' => null,
            ],
            'requiredHash' => [
                'name'     => 'abc',
                'value'    => 123,
                'optional' => '2015-09-03T12:00:00+0000',
            ],
        ];

        $client = static::createRestClient();
        $client->post('/testcase/requiredHash/', $data);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            [
                (object) [
                    'propertyPath'  => 'data.optionalHash.name',
                    'message'       => 'This value should not be blank.',
                ],
                (object) [
                    'propertyPath'  => 'data.optionalHash.value',
                    'message'       => 'This value should not be blank.',
                ],
                (object) [
                    'propertyPath'  => 'data.optionalHash.requiredSubHash.name',
                    'message'       => 'This value should not be blank.',
                ],
                (object) [
                    'propertyPath'  => 'data.optionalHash.requiredSubHash.value',
                    'message'       => 'This value should not be blank.',
                ],
                (object) [
                    'propertyPath'  => 'data.requiredHash.requiredSubHash.name',
                    'message'       => 'This value should not be blank.',
                ],
                (object) [
                    'propertyPath'  => 'data.requiredHash.requiredSubHash.value',
                    'message'       => 'This value should not be blank.',
                ],
            ],
            $client->getResults()
        );
    }

    /**
     * Test POST method with empty required hash
     *
     * @return void
     */
    public function testPostWithEmptyRequiredHash()
    {
        $data = [
            'name'         => __METHOD__,
            'requiredHash' => [
                'name'     => null,
                'value'    => null,
                'optional' => null,
            ],
        ];

        $client = static::createRestClient();
        $client->post('/testcase/requiredHash/', $data);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            [
                (object) [
                    'propertyPath'  => 'data.requiredHash.name',
                    'message'       => 'This value should not be blank.',
                ],
                (object) [
                    'propertyPath'  => 'data.requiredHash.value',
                    'message'       => 'This value should not be blank.',
                ],
                (object) [
                    'propertyPath'  => 'data.requiredHash.requiredSubHash.name',
                    'message'       => 'This value should not be blank.',
                ],
                (object) [
                    'propertyPath'  => 'data.requiredHash.requiredSubHash.value',
                    'message'       => 'This value should not be blank.',
                ],
            ],
            $client->getResults()
        );
    }
}
