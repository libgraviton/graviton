<?php

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;

class AppControllerTest extends RestTestCase
{
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

    public function testFindAll()
    {
        $this->client->request('GET', '/core/app');
        $results = $this->loadJsonFromClient($this->client);

        $this->assertEquals(
            2,
            count($results)
        );
        $this->assertEquals('hello', $results[0]->name);
        $this->assertEquals('Hello World!', $results[0]->title);
        $this->assertEquals(true, $results[0]->showInMenu);

        $this->assertEquals('admin', $results[1]->name);
        $this->assertEquals('Administration', $results[1]->title);
        $this->assertEquals(true, $results[1]->showInMenu);
    }

    public function testGetApp()
    {
        $this->client->request('GET', '/core/app/admin');
        $results = $this->loadJsonFromClient($this->client);

        $this->assertEquals('admin', $results->name);
        $this->assertEquals('Administration', $results->title);
        $this->assertEquals(true, $results->showInMenu);
    }

}
