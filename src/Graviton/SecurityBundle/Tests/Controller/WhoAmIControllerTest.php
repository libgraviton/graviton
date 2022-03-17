<?php
/**
 * functional test for /person/whoami
 */

namespace Graviton\SecurityBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use GravitonDyn\SecurityUserBundle\DataFixtures\MongoDB\LoadSecurityUserData;

/**
 * Basic functional test for /person/whoami.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class WhoAmIControllerTest extends RestTestCase
{

    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->loadFixturesLocal(
            [
                LoadSecurityUserData::class
            ]
        );
    }

    /**
     * Tests if request with no user gives us anonymous
     *
     * @return void
     */
    public function testWhoAmIActionNoUser()
    {
        $client = static::createRestClient();
        $client->request('GET', '/person/whoami');

        $this->assertSame('anonymous', $client->getResults()->username);
    }

    /**
     * Tests if request with not existing user gives us anonymous
     *
     * @return void
     */
    public function testWhoAmIActionNotExistingUserHeader()
    {
        $client = static::createRestClient();
        $client->request('GET', '/person/whoami', [], [], ['HTTP_X-GRAVITON-USER' => 'joe']);

        $this->assertSame('anonymous', $client->getResults()->username);
    }

    /**
     * Tests if request with existing user gives us the object -> wrongly cased spelling
     *
     * @return void
     */
    public function testWhoAmIActionExistingUserHeader()
    {
        $client = static::createRestClient();
        $client->request('GET', '/person/whoami', [], [], ['HTTP_X-GRAVITON-USER' => 'hANs']);

        $this->assertSame('hans', $client->getResults()->username);
        $this->assertSame('Hans Hofer', $client->getResults()->name);
        $this->assertSame('Randweg 33', $client->getResults()->street);
        $this->assertSame('100', $client->getResults()->id);
    }

    /**
     * Tests if request with not existing user gives us anonymous
     *
     * @return void
     */
    public function testWhoAmIActionNotExistingUserSubnet()
    {
        $client = static::createRestClient();
        $client->request('GET', '/person/whoami', [], [], ['HTTP_x-graviton-auth' => 'joe']);
        $client->request('GET', '/person/whoami');

        $this->assertSame('anonymous', $client->getResults()->username);
    }

    /**
     * Tests if request with existing user gives us the object -> wrongly cased spelling
     * -> subnet strategy only gives us SubnetUser; no additional information
     *
     * @return void
     */
    public function testWhoAmIActionExistingUserSubnet()
    {
        $client = static::createRestClient();
        $client->request('GET', '/person/whoami', [], [], ['HTTP_x-graviton-auth' => 'mANfreD']);

        $this->assertEqualsIgnoringCase('mANfreD', $client->getResults()->username);
        $this->assertObjectHasAttribute('name', $client->getResults());
        $this->assertObjectHasAttribute('street', $client->getResults());
        $this->assertObjectHasAttribute('id', $client->getResults());
    }

    /**
     * Tests if schema returns the right values
     *
     * @return void
     */
    public function testVersionsSchemaAction()
    {
        $client = static::createRestClient();
        $client->request('GET', '/schema/person/whoami');

        // just check it's a normal graviton schema..
        $this->assertIsString($client->getResults()->title);
    }
}
