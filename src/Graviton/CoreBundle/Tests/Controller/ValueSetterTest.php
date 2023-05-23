<?php
/**
 * ValueSetterTest class file
 */

namespace Graviton\CoreBundle\Tests\Controller;

use DateTimeInterface;
use Symfony\Component\HttpFoundation\Response;
use Graviton\TestBundle\Test\RestTestCase;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ValueSetterTest extends RestTestCase
{

    /**
     * test regexes
     *
     * @return void
     */
    public function testValueSetter()
    {
        $oldDate = '2015-12-10T10:02:16+0000';
        $oldDateDate = \DateTime::createFromFormat(DateTimeInterface::ATOM, $oldDate);

        $docId = uniqid('test');
        $data = [
            'id' => $docId,
            'alwaysCurrentDate' => $oldDate
        ];

        // should fail as dates must be empty
        $client = static::createRestClient();
        $client->put('/testcase/value-setter/'.$docId, $data);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        // get it..
        $client = static::createRestClient();
        $client->request('GET', '/testcase/value-setter/'.$docId);

        $obj = $client->getResults();
        $this->assertNotNull($obj->alwaysCurrentDate);
        $savedDate = \DateTime::createFromFormat(DateTimeInterface::ATOM, $obj->alwaysCurrentDate);

        // must be newer, not what we sent
        $this->assertTrue($savedDate > $oldDateDate);

        // wait 1 sec
        sleep(1);

        // update again with old -> send old again!
        $client = static::createRestClient();
        $client->put('/testcase/value-setter/'.$docId, $data);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        // get it once again
        $client = static::createRestClient();
        $client->request('GET', '/testcase/value-setter/'.$docId);

        $obj = $client->getResults();
        $this->assertNotNull($obj->alwaysCurrentDate);
        $savedDateNumberTwo = \DateTime::createFromFormat(DateTimeInterface::ATOM, $obj->alwaysCurrentDate);

        // make sure it was updated!
        $this->assertTrue($savedDateNumberTwo > $savedDate);
    }
}
