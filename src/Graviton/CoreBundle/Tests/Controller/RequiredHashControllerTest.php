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
    public function setUp() : void
    {
        if (!class_exists('GravitonDyn\TestCaseRequiredHashBundle\DataFixtures\MongoDB\LoadTestCaseRequiredHashData')) {
            $this->markTestSkipped('TestCaseRequiredHashData definition is not loaded');
        }

        $this->loadFixturesLocal(
            ['GravitonDyn\TestCaseRequiredHashBundle\DataFixtures\MongoDB\LoadTestCaseRequiredHashData']
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
     * yielding for incremental error checking
     *
     * @return void
     */
    public function postWithoutFieldInOptionalHashDataProvider()
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

        yield 'base-test' => [$data, 'optionalHash.requiredSubHash'];

        $data['optionalHash']['requiredSubHash'] = [];

        yield 'with-optional-hash' => [$data, 'optionalHash.value'];

        $data['optionalHash']['value'] = 222;

        yield 'with-optional-hash-value' => [$data, 'optionalHash.requiredSubHash.name'];

        $data['optionalHash']['requiredSubHash'] = ['name' => 'hans'];

        yield 'with-optional-hash-req-sub-name' => [$data, 'optionalHash.requiredSubHash.value'];

        $data['optionalHash']['requiredSubHash']['value'] = 22;

        yield 'with-optional-hash-req-sub-val' => [$data, 'requiredHash.optionalSubHash.value'];

        $data['requiredHash']['optionalSubHash']['value'] = 22;

        // all ok!
        yield 'all-ok' => [$data, null];
    }

    /**
     * Test POST method without field in optional hash
     *
     * @dataProvider postWithoutFieldInOptionalHashDataProvider
     *
     * @return void
     */
    public function testPostWithoutFieldInOptionalHash($data, $complainField)
    {
        $client = static::createRestClient();
        $client->post('/testcase/requiredhash/', $data);

        if (!empty($complainField)) {
            $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
            $this->assertEquals($complainField, $client->getResults()[1]->propertyPath);
        } else {
            $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        }
    }

    /**
     * data provider
     *
     * @return \Generator data
     */
    public function postWithoutRequiredHashDataProvider(): \Generator
    {
        $data = [
            'name' => __METHOD__,
            'requiredHash' => []
        ];

        yield 'missing' => [$data, 'requiredHash.name'];

        $data['requiredHash'] = ['name' => 'str'];

        yield 'with-name' => [$data, 'requiredHash.requiredSubHash'];

        $data['requiredHash']['requiredSubHash'] = new \stdClass();

        yield 'with-req-subhash' => [$data, 'requiredHash.value'];

        // wrong type
        $data['requiredHash']['value'] = 'str';

        yield 'with-req-val' => [$data, 'requiredHash.value'];

        $data['requiredHash']['value'] = 33;

        yield 'with-req-val-int' => [$data, 'requiredHash.requiredSubHash.name'];

        $data['requiredHash']['requiredSubHash']->name = 'hans';

        yield 'with-req-sub-name' => [$data, 'requiredHash.requiredSubHash.name'];

        $data['requiredHash']['requiredSubHash']->value = 33;

        yield 'with-req-all-ok' => [$data, null];
    }

    /**
     * Test POST method without required hash
     *
     * @dataProvider postWithoutRequiredHashDataProvider
     *
     * @return void
     */
    public function testPostWithoutRequiredHash($data, $complainField)
    {
        $client = static::createRestClient();
        $client->post('/testcase/requiredhash/', $data);

        if (!empty($complainField)) {
            $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
            $this->assertEquals($complainField, $client->getResults()[1]->propertyPath);
        } else {
            $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        }
    }

    public function postWithEmptyOptionalHashDataProvider(): \Generator
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

        //yield 'basic' => [$data, 'optionalHash.requiredSubHash'];

        $data['optionalHash']['requiredSubHash'] = new \stdClass();

        //yield 'opt-req-sub' => [$data, 'optionalHash.name'];

        $data['optionalHash']['name'] = 'str';

        //yield 'opt-name' => [$data, 'optionalHash.name'];

        $data['optionalHash']['value'] = 'str';

        //yield 'opt-val' => [$data, 'optionalHash.value'];

        $data['optionalHash']['value'] = 11;

        //yield 'opt-val-int' => [$data, 'optionalHash.optional'];

        $data['optionalHash']['optional'] = '2015-09-03T12:00:00+0000';

        //yield 'opt-opt-dat' => [$data, 'optionalHash.optional'];

        //$data['requiredHash']['requiredSubHash']->name = 'str';

        yield 'with-req-sub-name' => [$data, 'requiredHash.requiredSubHash.value'];

        //$data['requiredHash']['requiredSubHash']->value = 33;

    }

    /**
     * Test POST method with empty optional hash
     *
     * @dataProvider postWithEmptyOptionalHashDataProvider
     *
     * @return void
     */
    public function testPostWithEmptyOptionalHash($data, $complainField)
    {
        $client = static::createRestClient();
        $client->post('/testcase/requiredhash/', $data);
        if (!empty($complainField)) {
            $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
            $this->assertEquals($complainField, $client->getResults()[1]->propertyPath);
        } else {
            $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        }
        return;

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
        $this->assertStringNotContainsString('realId', $response->getContent());
    }
}
