<?php
/**
 * functional test for /person/whoami
 */

namespace Graviton\SecurityBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use GravitonDyn\SecurityUserBundle\DataFixtures\MongoDB\LoadSecurityUserData;
use Symfony\Component\BrowserKit\Cookie;

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
    public function setUp()
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
    public function testWhoAmIActionNotExistingUserCookie()
    {
        $client = static::createRestClient();
        $client->getCookieJar()->set(new Cookie('x-graviton-user', 'joe'));
        $client->request('GET', '/person/whoami');

        $this->assertSame('anonymous', $client->getResults()->username);
    }

    /**
     * Tests if request with existing user gives us the object -> wrongly cased spelling
     *
     * @return void
     */
    public function testWhoAmIActionExistingUserCookie()
    {
        $client = static::createRestClient();
        $client->getCookieJar()->set(new Cookie('x-graviton-user', 'fREd'));

        $client->request('GET', '/person/whoami');

        $this->assertSame('fred', $client->getResults()->username);
        $this->assertSame('Fred Feuz', $client->getResults()->name);
        $this->assertSame('Kirchweg 33', $client->getResults()->street);
        $this->assertSame('200', $client->getResults()->id);
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

        $this->assertSame('mANfreD', $client->getResults()->username);
        $this->assertObjectNotHasAttribute('name', $client->getResults());
        $this->assertObjectNotHasAttribute('street', $client->getResults());
        $this->assertObjectNotHasAttribute('id', $client->getResults());
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
        $response = $client->getResponse();

        $this->assertEquals(
            '{"title":"Who am I service","description":"Authenticated user verification service","required":true,'.
            '"searchable":[],"username":{"title":"The username of the logged in consultant",'.
            '"description":"your username","type":"string"},"additionalProperties":true}',
            $response->getContent()
        );
        $this->assertInternalType('string', $response->getContent());
    }
}
