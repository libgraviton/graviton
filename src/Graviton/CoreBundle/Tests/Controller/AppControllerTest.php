<?php

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;

class AppControllerTest extends RestTestCase
{
    public function testFindAll()
    {
        $client = static::createClient();

        $this->loadFixtures(
            array(
                'Graviton\CoreBundle\DataFixtures\MongoDB\LoadAppData'
            ),
            null,
            'doctrine_mongodb'
        );

        $client->request('GET', '/core/app');
        $results = $this->loadJsonFromClient($client);

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
}
