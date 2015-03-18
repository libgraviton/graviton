<?php
/**
 * Basic functional test for /i18n/language.
 */

namespace Graviton\I18nBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;

/**
 * Basic functional test for /i18n/translatable
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class TranslatableControllerTest extends RestTestCase
{
    /**
     * check that translatable record return correct CORS headers
     *
     * @return void
     */
    public function testOptionsHasCors()
    {
        $client = static::createRestClient();

        $client->request('OPTIONS', '/i18n/translatable/i18n-de-German');
        $this->assertCorsHeaders('GET, POST, PUT, DELETE, OPTIONS', $client->getResponse());
    }
}
