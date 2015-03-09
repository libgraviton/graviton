<?php
/**
 * Basic functional test for /i18n/language.
 */

namespace Graviton\I18nBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;

/**
 * Basic functional test for /i18n/language.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class LanguageControllerTest extends RestTestCase
{
    /**
     * @const complete content type string expected on a resouce
     */
    const CONTENT_TYPE = 'application/json; charset=UTF-8; profile=http://localhost/schema/i18n/language/';

    /**
     * check if a list of all languages can be optained
     *
     * @return void
     */
    public function testFindAll()
    {
        $client = static::createRestClient();
        $client->request('GET', '/i18n/language');

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::CONTENT_TYPE . 'collection', $response);

        // we assume that initially all systems will only know of the english lang
        $this->assertcount(1, $results);

        $this->assertEquals('en', $results[0]->id);

        $this->assertEquals('en', $response->headers->get('Content-Language'));

        $this->assertEquals('English', $results[0]->name->en);
    }

    /**
     * test add language and request both languages
     *
     * @return void
     */
    public function testAddAndUseNewLanguage()
    {
        $newLang = new \stdClass;
        $newLang->id = 'de';
        $newLang->name = new \stdClass;
        $newLang->name->en = 'German';

        $client = static::createRestClient();
        $client->post('/i18n/language', $newLang);

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseContentType(self::CONTENT_TYPE . 'item', $response);

        $this->assertEquals('de', $results->id);

        $this->assertEquals('en', $response->headers->get('Content-Language'));

        // client has to be rebuild since the AppKernel will be resetted after a request
        // which will unregister bundles registered by bundle loader.
        $client = static::createRestClient();
        $client->request('GET', '/i18n/language', array(), array(), array('HTTP_ACCEPT_LANGUAGE' => 'en,de'));

        $this->assertEquals('en, de', $client->getResponse()->headers->get('Content-Language'));

        // client has to be rebuild since the AppKernel will be resetted after a request
        // which will unregister bundles registered by bundle loader.
        $client = static::createRestClient();
        $client->request('GET', '/i18n/language/en', array(), array(), array('HTTP_ACCEPT_LANGUAGE' => 'en,de'));

        $results = $client->getResults();

        $this->assertEquals('English', $results->name->en);
        $this->assertEquals('Englisch', $results->name->de);

        // client has to be rebuild since the AppKernel will be resetted after a request
        // which will unregister bundles registered by bundle loader.
        $client = static::createRestClient();
        $client->request('GET', '/i18n/translatable/i18n-de-German');

        $this->assertEquals('i18n', $client->getResults()->domain);
        $this->assertEquals('de', $client->getResults()->locale);
        $this->assertEquals('German', $client->getResults()->original);
    }

    /**
     * check that we do not return unknown languages
     *
     * @return void
     */
    public function testDontReturnUnknownLanguage()
    {
        $client = static::createRestClient();

        $client->request('GET', '/i18n/language/en', array(), array(), array('HTTP_ACCEPT_LANGUAGE' => 'en,de'));

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertEquals('en', $response->headers->get('Content-Language'));
        $this->assertEquals('English', $results->name->en);
        $this->assertFalse(isset($results->name->de));
    }

    /**
     * check for language schema
     *
     * @return void
     */
    public function testSchema()
    {
        $client = static::createRestClient();

        $client->request('OPTIONS', '/i18n/language', array(), array(), array('HTTP_ACCEPT_LANGUAGE' => 'en,de'));

        $results = $client->getResults();

        $this->assertEquals('A Language available for i18n purposes.', $results->items->description);
        $this->assertEquals(array('id', 'name'), $results->items->required);

        $properties = $results->items->properties;
        $this->assertEquals('string', $properties->id->type);
        $this->assertEquals('Language Tag', $properties->id->title);
        $this->assertEquals('A RFC2616 language tag.', $properties->id->description);

        $this->assertEquals('object', $properties->name->type);
        $this->assertEquals('Language', $properties->name->title);
        $this->assertEquals('Common name of a language.', $properties->name->description);
        $this->assertEquals('string', $properties->name->properties->en->type);

    }
}
