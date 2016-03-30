<?php
/**
 * functional test for /core/version
 */

namespace Graviton\SecurityBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;

/**
 * Basic functional test for /person/whoami.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class WhoAmIControllerTest extends RestTestCase
{

    /**
     * Tests if get request returns data in right format
     *
     * @return void
     */
    public function testWhoAmIAction()
    {
        $client = static::createRestClient();
        $client->request('GET', '/person/whoami');
        $response = $client->getResponse();

        $this->assertContains('"username":', $response->getContent());
        $this->assertInternalType('string', $response->getContent());
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
