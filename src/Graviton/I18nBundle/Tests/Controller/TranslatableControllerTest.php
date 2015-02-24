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
     * @const complete content type string expected on a resouce
     */
    const CONTENT_TYPE = 'application/json; charset=UTF-8; profile=http://localhost/schema/i18n/translatable/';

   /**
     * check for language schema
     *
     * @return void
     */
    public function testSchema()
    {
        $client = static::createRestClient();

        $client->request('OPTIONS', '/i18n/translatable', array(), array(), array('HTTP_ACCEPT_LANGUAGE' => 'en,de'));

        $results = $client->getResults();
        $response = $client->getResponse();
        var_dump($results);

        $this->assertEquals('A Translatable string available for i18n purposes.', $results->items->description);
        $this->assertEquals('*', $response->headers->get('Access-Control-Allow-Origin'));
    }
}
