<?php
/**
 * Tester to Controller
 */
namespace Graviton\AuditTrackingBundle\Tests\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Graviton\AuditTrackingBundle\Manager\StoreManager;
use Graviton\CoreBundle\Document\App;
use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Basic functional test for /core/app.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DefaultControllerTest extends RestTestCase
{
    /** Name to be used in test */
    const TEST_APP_ID = 'audit-id';
    
    /** @var  DocumentManager */
    private $documentManager;

    /**
     * Ensure a clean Db for test
     *
     * @return void
     */
    public function setUp()
    {
        // We only delete on first start up.
        if (!$this->documentManager) {
            $this->documentManager = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');

            /** @var App $app */
            $app = $this->documentManager->find(get_class(new App()), self::TEST_APP_ID);
            if ($app) {
                $this->documentManager->remove($app);
                $this->documentManager->flush();
            }
        }
    }

    /**
     * @return \stdClass test object
     */
    private function getTestObj()
    {
        $new = new \stdClass();
        $new->id = self::TEST_APP_ID;
        $new->showInMenu = false;
        $new->order = 321;
        $new->name = new \stdClass();
        $new->name->en = 'audit en language name';
        $new->name->de = 'audit de language name';
        return $new;
    }
    
    /**
     * Insert a new APP element
     *
     * @return void
     */
    public function testInsertItem()
    {
        $new = $this->getTestObj();

        $client = static::createRestClient();
        $client->put('/core/app/'.self::TEST_APP_ID, $new);
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        
        // Lets check if the Audit Event was there and in header
        $header = $response->headers->get(StoreManager::AUDIT_HEADER_KEY);
        $this->assertNotEmpty($header, 'The expected audit header was not set as expected');

        // Get the data and hcek for a inserted new event
        $client = static::createRestClient();
        $client->request('GET', '/auditing/?eq(thread,string:'.$header.')');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $results = $client->getResults();

        $this->assertEquals(1, count($results), 'By default, only one result. Only insert into DB');
        $event = $results[0];
        $this->assertEquals('insert', $event->{'action'});
        $this->assertEquals('collection', $event->{'type'});
        $this->assertEquals('App', $event->{'collectionName'});
        $this->assertEquals(self::TEST_APP_ID, $event->{'collectionId'});
    }

    /**
     * Update an APP element
     *
     * @return void
     */
    public function testUpdateItem()
    {
        $new = $this->getTestObj();

        $client = static::createRestClient();
        $client->put('/core/app/'.self::TEST_APP_ID, $new);
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $client = static::createRestClient();
        $patchJson = json_encode(
            [
                [
                    'op' => 'replace',
                    'path' => '/name/en',
                    'value' => 'Test App audit Patched'
                ]
            ]
        );
        $client->request('PATCH', '/core/app/' . self::TEST_APP_ID, [], [], [], $patchJson);
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Lets check if the Audit Event was there and in header
        $header = $response->headers->get(StoreManager::AUDIT_HEADER_KEY);
        $this->assertNotEmpty($header, 'The expected audit header was not set as expected');

        // Get the data and hcek for a inserted new event
        $client = static::createRestClient();
        $client->request('GET', '/auditing/?eq(thread,string:'.$header.')');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $results = $client->getResults();

        $this->assertEquals(1, count($results), 'By default, only one result. Only basic logs are active');
        $event = $results[0];
        $this->assertEquals('update', $event->{'action'});
        $this->assertEquals('collection', $event->{'type'});
        $this->assertEquals('App', $event->{'collectionName'});
        $this->assertEquals(self::TEST_APP_ID, $event->{'collectionId'});
    }
}
