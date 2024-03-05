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
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class VersionControllerTest extends RestTestCase
{

    /**
     * Tests if get request returns data in right schema format
     *
     * @return void
     */
    public function testVersionsAction()
    {
        $client = static::createRestClient();
        $client->request('GET', '/core/version');
        $response = $client->getResponse();

        $this->assertStringContainsString('"self":', $response->getContent());
        $this->assertIsString($response->getContent());

        $tagRegExp = '^([v]?[0-9]+\.[0-9]+\.[0-9]+)(-[0-9a-zA-Z.]+)?(\+[0-9a-zA-Z.]+)?$';
        $branchRegExp = '^((dev\-){1}[0-9a-zA-Z\.\/\-\_]+)';
        $secondDevRegExp = '^(.*)-dev@(.*)';
        $regExp = sprintf('/%s|%s|%s/', $tagRegExp, $branchRegExp, $secondDevRegExp);

        $content = json_decode($response->getContent(), true);

        // no need to check php as there are many different variations
        if (isset($content['versions']['php'])) {
            unset($content['versions']['php']);
        }

        $this->assertFalse(empty($content['versions']));
    }

    /**
     * Tests if schema returns the right values
     *
     * @return void
     */
    public function testVersionsSchemaAction()
    {
        $client = static::createRestClient();
        $client->request('GET', '/schema/core/version/openapi.json');

        $this->assertTrue(isset($client->getResults()->paths->{'/core/version'}));
    }
}
