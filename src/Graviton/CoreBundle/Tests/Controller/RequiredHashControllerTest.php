<?php
/**
 * RequiredHashControllerTest class file
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
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
        $client->post('/testcase/requiredhash/', $data);
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
        $client->post('/testcase/requiredhash/', $data);
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
        $client->post('/testcase/requiredhash/', $data);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            [
                (object) [
                    'propertyPath'  => 'optionalHash.value',
                    'message'       => 'The property value is required',
                ],
                (object) [
                    'propertyPath'  => 'optionalHash.requiredSubHash',
                    'message'       => 'The property requiredSubHash is required',
                ],
                (object) [
                    'propertyPath'  => 'requiredHash.optionalSubHash.value',
                    'message'       => 'The property value is required',
                ],
            ],
            $client->getResults()
        );

        // add requiredSubHash and check deeper properties
        $data['optionalHash']['requiredSubHash'] = (object) [];
        $client = static::createRestClient();
        $client->post('/testcase/requiredhash/', $data);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            [
                (object) [
                    'propertyPath'  => 'optionalHash.value',
                    'message'       => 'The property value is required',
                ],
                (object) [
                    'propertyPath'  => 'optionalHash.requiredSubHash.name',
                    'message'       => 'The property name is required',
                ],
                (object) [
                    'propertyPath'  => 'optionalHash.requiredSubHash.value',
                    'message'       => 'The property value is required',
                ],
                (object) [
                    'propertyPath'  => 'requiredHash.optionalSubHash.value',
                    'message'       => 'The property value is required',
                ]
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
            'requiredHash' => (object) []
        ];

        $client = static::createRestClient();
        $client->post('/testcase/requiredhash/', $data);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            [
                (object) [
                    'propertyPath'  => 'requiredHash.name',
                    'message'       => 'The property name is required',
                ],
                (object) [
                    'propertyPath'  => 'requiredHash.value',
                    'message'       => 'The property value is required',
                ],
                (object) [
                    'propertyPath'  => 'requiredHash.requiredSubHash',
                    'message'       => 'The property requiredSubHash is required',
                ]
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
        $client->post('/testcase/requiredhash/', $data);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            [
                (object) [
                    'propertyPath'  => 'optionalHash.requiredSubHash',
                    'message'       => 'The property requiredSubHash is required',
                ],
                (object) [
                    'propertyPath'  => 'optionalHash.name',
                    'message'       => 'NULL value found, but a string is required',
                ],
                (object) [
                    'propertyPath'  => 'optionalHash.value',
                    'message'       => 'NULL value found, but an integer is required',
                ],
                (object) [
                    'propertyPath'  => 'requiredHash.requiredSubHash',
                    'message'       => 'The property requiredSubHash is required',
                ]
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
        $client->post('/testcase/requiredhash/', $data);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            [
                (object) [
                    'propertyPath'  => 'requiredHash.requiredSubHash',
                    'message'       => 'The property requiredSubHash is required',
                ],
                (object) [
                    'propertyPath'  => 'requiredHash.name',
                    'message'       => 'NULL value found, but a string is required',
                ],
                (object) [
                    'propertyPath'  => 'requiredHash.value',
                    'message'       => 'NULL value found, but an integer is required',
                ]
            ],
            $client->getResults()
        );
    }

    /**
     * check that schema does not contain realId artefacts
     *
     * @return void
     */
    public function testCollectionHasNoRealId()
    {
        $client = static::createRestclient();
        $client->request('GET', '/schema/testcase/requiredhash/collection');

        $response = $client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertNotContains('realId', $response->getContent());
    }
}
