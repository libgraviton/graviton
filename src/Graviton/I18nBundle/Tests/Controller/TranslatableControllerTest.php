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
     * check for language schema
     *
     * @return void
     */
    public function testOptionsHasCors()
    {
        $client = static::createRestClient();

        $client->request('OPTIONS', '/i18n/translatable');
        $this->assertEquals('*', $client->getResponse()->headers->get('Access-Control-Allow-Origin'));
    }
}
