<?php
/**
 * test a readOnly service
 */

namespace Graviton\Tests\Rest\Service;

use Graviton\Tests\RestTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Functional test
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ReadOnlyFieldTest extends RestTestCase
{
    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->loadFixturesLocal(
            array(
                'GravitonDyn\TestCaseReadOnlyFieldBundle\DataFixtures\MongoDB\LoadTestCaseReadOnlyFieldData',
            )
        );
    }

    /**
     * see if the readOnly fields are denied as they should
     *
     * @return void
     */
    public function testReadOnlyDenying()
    {
        $client = static::createRestClient();
        $client->request('GET', '/testcase/readonlyfield/101');
        $data = $client->getResults();

        $data->denied = 'cannot change';
        $data->deniedArray[0] = 'also no change';
        $data->deniedArray[1] = 'also no change';
        $data->deniedObject->denied = 'whatever you do, do not change this';
        $data->deniedObject->allowed = 'can do';
        $data->allowed = 'this can be changed';

        $client = static::createRestClient();
        $client->put('/testcase/readonlyfield/101', $data);
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), $response->getContent());

        // should complain about /denied
        $this->assertEquals('/denied', $client->getResults()[0]->propertyPath);

        // don't send denied
        unset($data->denied);

        $client->put('/testcase/readonlyfield/101', $data);
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), $response->getContent());

        // first array element!
        $this->assertEquals('/deniedArray/0', $client->getResults()[0]->propertyPath);

        unset($data->deniedArray[0]);

        $client->put('/testcase/readonlyfield/101', $data);
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), $response->getContent());

        // second array element!
        $this->assertEquals('/deniedArray/0', $client->getResults()[0]->propertyPath);

        unset($data->deniedArray);

        $client->put('/testcase/readonlyfield/101', $data);
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), $response->getContent());

        $this->assertEquals('/deniedObject/denied', $client->getResults()[0]->propertyPath);
    }

    /**
     * see if the allowed fields can be updated
     *
     * @return void
     */
    public function testReadOnlyChangeAllowedFields()
    {
        $client = static::createRestClient();
        $client->request('GET', '/testcase/readonlyfield/101');
        $data = $client->getResults();

        $data->allowed = 'this can be changed';
        $data->deniedObject->allowed = 'can do';

        $client = static::createRestClient();
        $client->put('/testcase/readonlyfield/101', $data);
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode(), $response->getContent());
    }

    /**
     * Check for readonly property that should be updated only if it was not there.
     *
     * @return void
     */
    public function testUpdateEmptyReadOnlyField()
    {
        $client = static::createRestClient();
        $data = new \stdClass();
        $data->id = '101_2';
        $data->allowed = 'can be edited';

        $client->put('/testcase/readonlyfield/101_2', $data);
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode(), $response->getContent());

        // But should be able to add it, as it was never there.
        $data->denied = 'can not be edited';

        $client->put('/testcase/readonlyfield/101_2', $data);
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode(), $response->getContent());

        $client->request('GET', '/testcase/readonlyfield/101_2');
        $savedData = $client->getResults();

        $data = (array) $data;
        $savedData = (array) $savedData;
        ksort($data);
        ksort($savedData);

        $this->assertEquals(json_encode($data), json_encode($savedData));
    }
}
