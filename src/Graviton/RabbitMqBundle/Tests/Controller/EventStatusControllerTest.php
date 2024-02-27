<?php
/**
 * functional test for /event/status
 */

namespace Graviton\RabbitMqBundle\Tests\Controller;

use Graviton\RabbitMqBundle\Producer\Dummy;
use Graviton\TestBundle\Test\RestTestCase;
use Laminas\Diactoros\Uri;
use Symfony\Component\HttpFoundation\Response;

/**
 * Functional test for /event/status.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class EventStatusControllerTest extends RestTestCase
{

    /**
     * custom environment
     *
     * @var string
     */
    protected static $environment = 'test_restricted';

    /**
     * custom client options
     *
     * @var string[]
     */
    private $clientOptions = ['environment' => 'test_restricted'];

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
        $statusEntry->action = (object) [
            '$ref' => 'http://localhost/event/action/abba'
        ];

        $status->status = [$statusEntry];

        $client = static::createRestClient($this->clientOptions);
        $client->put('/event/status/mynewstatus', $status);

        $this->assertNull($client->getResults());
        $this->assertNull($client->getResponse()->headers->get('Location'));
        $this->assertEquals(204, $client->getResponse()->getStatusCode());

        // get our object again
        $client = static::createRestClient($this->clientOptions);
        $client->request('GET', '/event/status/mynewstatus');
        $results = $client->getResults();

        $this->assertEquals('opened', $results->status[0]->status);
        $this->assertEquals('http://localhost/event/action/abba', $results->status[0]->action->{'$ref'});

        // set invalid status
        $results->status[0]->status = 'thinking';

        $client = static::createRestClient($this->clientOptions);
        $client->put('/event/status/mynewstatus', $results);
        $results = $client->getResults();

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $this->assertEquals("status.0.status", $results[1]->propertyPath);
        $this->assertStringContainsString(
            "Value must be present in the enum",
            $results[1]->message
        );
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

        $client = static::createRestClient($this->clientOptions);
        $client->put('/event/status/mynewstatus', $status);

        $this->assertNull($client->getResults());
        $this->assertNull($client->getResponse()->headers->get('Location'));
        $this->assertEquals(204, $client->getResponse()->getStatusCode());

        // get our object again
        $client = static::createRestClient($this->clientOptions);
        $client->request('GET', '/event/status/mynewstatus');
        $results = $client->getResults();

        $this->assertEquals('testworker', $results->information[0]->workerId);
        $this->assertEquals('info', $results->information[0]->type);
        $this->assertEquals('see the attached document', $results->information[0]->content);
        $this->assertEquals('http://localhost/core/app/admin', $results->information[0]->{'$ref'});

        // set invalid information type
        $results->information[0]->type = 'bogus';

        $client = static::createRestClient($this->clientOptions);
        $client->put('/event/status/mynewstatus', $results);
        $results = $client->getResults();

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $this->assertEquals("information.0.type", $results[1]->propertyPath);
        $this->assertStringContainsString(
            "Value must be present in the enum",
            $results[1]->message
        );
    }

    /**
     * Event Status Action
     *
     * @return void
     */
    public function testEventStatusActionStatus()
    {
        // Create a action and translation
        $action = new \stdClass();
        $action->id = 'new-worker-action-id';
        $action->description = new \stdClass();
        $action->description->en = "Some translated action";

        $client = static::createRestClient($this->clientOptions);
        $client->put('/event/action/'.$action->id, $action);

        // Check result
        $this->assertEquals(204, $client->getResponse()->getStatusCode());

        // get our object again
        $client = static::createRestClient($this->clientOptions);
        $client->request('GET', '/event/action/'.$action->id);
        $results = $client->getResults();
        $this->assertEquals($action->description->en, $results->description->en);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Creating the event
        $eventStatus = new \stdClass();
        $eventStatus->id = 'mynewstatus2';
        $eventStatus->createDate = '2015-09-24T07:21:24+0000';
        $eventStatus->eventName = 'document.test.app.create';

        $status = new \stdClass();
        $status->workerId = 'testworker';
        $status->status = 'opened';
        $status->action = new \stdClass();
        $status->action->{'$ref'} = 'http://localhost/event/action/'.$action->id;
        $eventStatus->status = [$status];

        // Save the status
        $client = static::createRestClient($this->clientOptions);
        $client->put('/event/status/mynewstatus2', $eventStatus);

        $this->assertNull($client->getResults());
        $this->assertNull($client->getResponse()->headers->get('Location'));
        $this->assertEquals(204, $client->getResponse()->getStatusCode());

        // get our object again, checking
        $client = static::createRestClient($this->clientOptions);
        $client->request('GET', '/event/status/mynewstatus2');
        $results = $client->getResults();

        $this->assertEquals('opened', $results->status[0]->status);
        $this->assertEquals('http://localhost/event/action/'.$action->id, $results->status[0]->action->{'$ref'});

        // set status through direct route
        $client = static::createRestClient($this->clientOptions);
        $client->put('/event/status/mynewstatus2/testworker/working/manfred', []);

        // fetch it
        $client = static::createRestClient($this->clientOptions);
        $client->request('GET', '/event/status/mynewstatus2');
        $results = $client->getResults();
        $this->assertEquals("working", $results->status[0]->status);
        $this->assertTrue(str_ends_with($results->status[0]->action->{'$ref'}, '/manfred'));
    }


    /**
     * Verifies the correct workflow of the ResponseListener
     *
     * @return void
     */
    public function testEventStatusLinkResponseListener()
    {
        // Create a test worker
        $worker = new \stdClass();
        $worker->id = 'test-worker-listener';
        $worker->subscription = [];
        $event = new \stdClass();
        $event->event = 'document.app.app.create';
        $worker->subscription[] = $event;
        $event = new \stdClass();
        $event->event = 'document.app.app.update';
        $worker->subscription[] = $event;

        $client = static::createRestClient($this->clientOptions);
        $client->put('/event/worker/' . $worker->id, $worker);
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode(), $response->getContent());

        $testAppId = uniqid('testapp-');
        $testApp = new \stdClass();
        $testApp->id = $testAppId;
        $testApp->name = new \stdClass();
        $testApp->name->en = "test-event-app";
        $testApp->showInMenu = false;

        $client = static::createRestClient($this->clientOptions);
        /** @var Dummy $dbProducer */
        $dbProducer = $client->getContainer()->get('graviton.rabbitmq.producer.extamqp');
        $dbProducer->resetEventList();

        $client->put(
            '/core/app/' . $testApp->id,
            $testApp,
            [],
            [],
            [
                'HTTP_GRAVITON_USER' => 'tester',
                'HTTP_X-GRAVITON-CLIENT' => '555'
            ]
        );

        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode(), $response->getContent());
        // the worker relative our cannot be in the Link header
        $this->assertStringNotContainsString('backendalias:9443', $response->headers->get('link'));
        $this->assertStringContainsString('eventStatus', $response->headers->get('link'));

        // get again
        $dbProducer = $client->getContainer()->get('graviton.rabbitmq.producer.extamqp');

        /** @var Dummy $dbProducer */
        $events = $dbProducer->getEventList();

        $this->assertCount(1, $events);
        $data = json_decode($events[0], true);

        $this->assertEquals('document.app.app.update', $data['event']);
        $this->assertEquals('anonymous', $data['coreUserId']);
        $this->assertEquals('https://backendalias:9443/core/app/'.$testApp->id, $data['document']['$ref']);
        $this->assertStringContainsString('https://backendalias:9443/event/status/', $data['status']['$ref']);

        // get EventStatus id
        $url = new Uri($data['status']['$ref']);
        $urlParts = explode('/', $url->getPath());
        $eventStatusId = $urlParts[3];

        // check transient headers
        $this->assertEquals(2, count($data['transientHeaders']));
        $this->assertEquals('tester', $data['transientHeaders']['graviton_user']);
        $this->assertEquals('555', $data['transientHeaders']['x-graviton-client']);

        // A failing event should not be published
        // using patch
        $patchObject = json_encode(
            [
                [
                    'op' => 'replace',
                    'path' => '/lastModified',
                    'value' => '2014-10-03T20:10:05+2000'
                ]
            ]
        );
        $client->request('PATCH', '/core/app/' . $testApp->id, [], [], [], $patchObject);
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_NOT_MODIFIED, $response->getStatusCode(), $response->getContent());

        /** @var Dummy $dbProducer */
        $events = $dbProducer->getEventList();
        $this->assertCount(1, $events);

        // With a wrong param S
        $testApp->showInMenuS = false;
        $client->put('/core/app/' . $testApp->id, $testApp);
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), $response->getContent());

        /** @var Dummy $dbProducer */
        $events = $dbProducer->getEventList();
        $this->assertCount(1, $events);

        // check that another 'clientId' cannot request the eventstatus
        $client = static::createRestClient($this->clientOptions);
        $client->request('GET', '/event/status/'.$eventStatusId, [], [], ['HTTP_X-GRAVITON-CLIENT' => '500']);
        $response = $client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());

        // check that we can get it back
        $client = static::createRestClient($this->clientOptions);
        $client->request('GET', '/event/status/'.$eventStatusId, [], [], ['HTTP_X-GRAVITON-CLIENT' => '555']);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }
}
