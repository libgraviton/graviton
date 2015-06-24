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

        $results = $client->getResults();
        $this->assertContains('id', $results->required);
    }

    /**
     * validate linking of objects
     *
     * @return void
     */
    public function testContainsReference()
    {
        $client = static::createRestClient();

        $client->request('GET', '/i18n/translatable');
        $results = $client->getResults();

        $this->assertEquals('http://localhost/i18n/language/de', $results[0]->language->{'$ref'});
    }

    /**
     * validate linking of objects in item
     *
     * @return void
     */
    public function testItemContainsReference()
    {
        $client = static::createRestClient();

        $client->request('GET', '/i18n/translatable/i18n-de-English');
        $results = $client->getResults();

        $this->assertEquals('http://localhost/i18n/language/de', $results->language->{'$ref'});
    }

    /**
     * validate creating new translatables
     *
     * @return void
     */
    public function testCreateTranslatableWithReference()
    {
        $value = new \stdClass;
        $value->id = 'i18n-de-French';
        $value->domain = 'i18n';
        $value->locale = 'de';
        $value->original = 'French';
        $value->translated = 'Französisch';
        $value->isLocalized = true;
        $value->language = new \stdClass;
        $value->language->{'$ref'} = 'http://localhost/i18n/language/de';

        $client = static::createRestClient();

        $client->post('/i18n/translatable', $value);
        $response = $client->getResponse();

        $client = static::createRestClient();
        $client->request('GET', $response->headers->get('Location'));
        $results = $client->getResults();

        $this->assertEquals('http://localhost/i18n/language/de', $results->language->{'$ref'});
    }
}
