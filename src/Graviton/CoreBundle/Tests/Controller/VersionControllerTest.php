<?php
/**
 * functional test for /core/version
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
class VersionControllerTest extends RestTestCase
{

    /**
     * Tests if get request returns data in right schema format
     *
     * @return void
     */
    public function testGetAllAction()
    {
        $client = static::createRestClient();
        $client->request('GET', '/core/version/');
        $response = $client->getResponse();

        $this->assertContains('"self":', $response->getContent());
        $this->assertInternalType('string', $response->getContent());
    }

    /**
     * Tests if error message is shown
     *
     * @return void
     */
    public function testWrongParamName()
    {
        $client = static::createRestClient();
        $client->request('GET', '/core/version/NotExsiting');
        $response = $client->getResponse();

        $this->assertEquals('{"error":"This id could not be resolved"}', $response->getContent());
    }
}
