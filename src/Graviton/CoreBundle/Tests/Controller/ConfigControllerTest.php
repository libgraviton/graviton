<?php
/**
 * functional test for /core/config
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;

/**
 * Basic functional test for /core/config.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ConfigControllerTest extends RestTestCase
{

    /**
     * setup client and load fixtures
     *
     * @return void
     */
    public function setUp()
    {
        $this->loadFixtures(
            array(
                'GravitonDyn\ConfigBundle\DataFixtures\MongoDB\LoadConfigData'
            ),
            null,
            'doctrine_mongodb'
        );
    }

    /**
     * We need to make sure that our Link headers are properly encoded for our RQL parser.
     * This test tries to ensure that as we have resources named-like-this in /core/config.
     *
     * @return void
     */
    public function testLinkHeaderEncoding()
    {
        $correctlyEncodedString = 'tablet%252Dhello%252Dmessage';

        $client = static::createRestClient();
        $client->request('GET', '/core/config?q=eq(id,'.$correctlyEncodedString.')');
        $response = $client->getResponse();

        $this->assertContains($correctlyEncodedString, $response->headers->get('Link'));
        $this->assertEquals(1, count($client->getResults()));
    }
}
