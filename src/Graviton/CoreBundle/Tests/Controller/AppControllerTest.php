<?php
/**
 * functional test for /core/app
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;

/**
 * Basic functional test for /core/app.
 *
 * @category GravitonCoreBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class AppControllerTest extends RestTestCase
{
    /**
     * setup client and load fixtures
     *
     * @return void
     */
    public function setUp()
    {
        $this->client = static::createClient();

        $this->loadFixtures(
            array(
                'Graviton\CoreBundle\DataFixtures\MongoDB\LoadAppData'
            ),
            null,
            'doctrine_mongodb'
        );
    }

    /**
     * check if all fixtures are returned on GET
     *
     * @return void
     */
    public function testFindAll()
    {
        $this->client->request('GET', '/core/app');
        $results = $this->loadJsonFromClient($this->client);
        $headers = $this->client->getResponse()->headers;

        $this->assertEquals(
            'application/vnd.graviton.core.app+json; charset=UTF-8',
            $headers->get('Content-Type')
        );

        $this->assertEquals(
            2,
            count($results)
        );
        $this->assertEquals('hello', $results[0]->id);
        $this->assertEquals('Hello World!', $results[0]->title);
        $this->assertEquals(true, $results[0]->showInMenu);

        $this->assertEquals('admin', $results[1]->id);
        $this->assertEquals('Administration', $results[1]->title);
        $this->assertEquals(true, $results[1]->showInMenu);
    }

    /**
     * test if we can get an app by id
     *
     * @return void
     */
    public function testGetApp()
    {
        $this->client->request('GET', '/core/app/admin');
        $results = $this->loadJsonFromClient($this->client);
        $headers = $this->client->getResponse()->headers;

        $this->assertEquals(
            'application/vnd.graviton.core.app+json; charset=UTF-8',
            $headers->get('Content-Type')
        );

        $this->assertEquals('admin', $results->id);
        $this->assertEquals('Administration', $results->title);
        $this->assertEquals(true, $results->showInMenu);
    }

    /**
     * test if we can create an app through POST
     *
     * @return void
     */
    public function testPostApp()
    {
        $testApp = new \stdClass;
        $testApp->id = 'new';
        $testApp->title = 'new Test App';
        $testApp->showInMenu = true;

        $this->client->request(
            'POST',
            '/core/app',
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'application/json'
            ),
            json_encode($testApp)
        );

        $results = $this->loadJsonFromClient($this->client);
        $headers = $this->client->getResponse()->headers;

        $this->assertEquals(
            'application/vnd.graviton.core.app+json; charset=UTF-8',
            $headers->get('Content-Type')
        );

        $this->assertEquals('new', $results->id);
        $this->assertEquals('new Test App', $results->title);
        $this->assertFalse($results->showInMenu);
    }
}
