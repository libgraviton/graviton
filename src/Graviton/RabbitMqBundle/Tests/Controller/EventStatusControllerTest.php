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

    /**
     * test to see if we can insert a status and if graviton complains about an invalid status
     *
     * @return void
     */
    public function testInvalidInformationType()
    {
        $status = new \stdClass();
        $status->id = 'mynewstatus';
        $status->createDate = '2015-09-24T07:21:24+0000';
        $status->eventName = 'document.test.app.create';

        $informationEntry = new \stdClass();
        $informationEntry->workerId = 'testworker';
        $informationEntry->type = 'info';
        $informationEntry->content = 'see the attached document';
        $informationEntry->{'$ref'} = 'http://localhost/core/app/admin';

        $status->information = [$informationEntry];

        $client = static::createRestClient();
        $client->put('/event/status/mynewstatus', $status);

        $this->assertNull($client->getResults());
        $this->assertNull($client->getResponse()->headers->get('Location'));
        $this->assertEquals(204, $client->getResponse()->getStatusCode());

        // get our object again
        $client = static::createRestClient();
        $client->request('GET', '/event/status/mynewstatus');
        $results = $client->getResults();

        $this->assertEquals('testworker', $results->information[0]->workerId);
        $this->assertEquals('info', $results->information[0]->type);
        $this->assertEquals('see the attached document', $results->information[0]->content);
        $this->assertEquals('http://localhost/core/app/admin', $results->information[0]->{'$ref'});

        // set invalid information type
        $results->information[0]->type = 'bogus';

        $client = static::createRestClient();
        $client->put('/event/status/mynewstatus', $results);
        $results = $client->getResults();

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $this->assertEquals("data.information[0].type", $results[0]->propertyPath);
        $this->assertContains("\"bogus\" is not a valid information type", $results[0]->message);
    }
}
