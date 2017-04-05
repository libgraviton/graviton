<?php
/**
 * test a readOnly service
 */

namespace Graviton\CoreBundle\Tests\Services;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Functional test
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ReadOnlyFieldTest extends RestTestCase
{
    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp()
    {
        $this->loadFixtures(
            array(
                'GravitonDyn\TestCaseReadOnlyFieldBundle\DataFixtures\MongoDB\LoadTestCaseReadOnlyFieldData',
            ),
            null,
            'doctrine_mongodb'
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
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        $this->assertEquals(
            $client->getResults(),
            [
                (object) [
                    'propertyPath' => 'denied',
                    'message' => 'The value "this is a denied field" is read only.'
                ],
                (object) [
                    'propertyPath' => 'deniedArray',
                    'message' => 'The value ["this is denied","this also"] is read only.'
                ],
                (object) [
                    'propertyPath' => 'deniedObject.denied',
                    'message' => 'The value "this is denied" is read only.'
                ]
            ]
        );
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
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
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
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        // But should be able to add it, as it was never there.
        $data->denied = 'can not be edited';

        $client->put('/testcase/readonlyfield/101_2', $data);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        $client->request('GET', '/testcase/readonlyfield/101_2');
        $savedData = $client->getResults();

        $data = (array) $data;
        $savedData = (array) $savedData;
        ksort($data);
        ksort($savedData);

        $this->assertEquals(json_encode($data), json_encode($savedData));
    }
}
