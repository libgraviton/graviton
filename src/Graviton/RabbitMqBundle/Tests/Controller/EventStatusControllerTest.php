<?php
/**
 * functional test for /event/status
 */

namespace Graviton\RabbitMqBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;

/**
 * Functional test for /event/status.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class EventStatusControllerTest extends RestTestCase
{

    /**
     * test to see if we can insert a status and if graviton complains about an invalid status
     *
     * @return void
     */
    public function testInvalidStatus()
    {
        $status = new \stdClass();
        $status->id = 'mynewstatus';
        $status->createDate = '2015-09-24T07:21:24+0000';
        $status->eventName = 'document.test.app.create';

        $statusEntry = new \stdClass();
        $statusEntry->workerId = 'testworker';
        $statusEntry->status = 'opened';

        $status->status = [$statusEntry];

        $client = static::createRestClient();
        $client->put('/event/status/mynewstatus', $status);

        $this->assertNull($client->getResults());
        $this->assertNull($client->getResponse()->headers->get('Location'));
        $this->assertEquals(204, $client->getResponse()->getStatusCode());

        // get our object again
        $client = static::createRestClient();
        $client->request('GET', '/event/status/mynewstatus');
        $results = $client->getResults();

        $this->assertEquals('opened', $results->status[0]->status);

        // set invalid status
        $results->status[0]->status = 'thinking';

        $client = static::createRestClient();
        $client->put('/event/status/mynewstatus', $results);
        $results = $client->getResults();

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $this->assertEquals("data.status[0].status", $results[0]->propertyPath);
        $this->assertContains("\"thinking\" is not a valid status string", $results[0]->message);
    }
}
