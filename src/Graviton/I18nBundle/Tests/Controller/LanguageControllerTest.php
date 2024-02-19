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
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class LanguageControllerTest extends RestTestCase
{
    /**
     * @const schema url
     */
    const SCHEMA_URL = 'http://localhost/schema/i18n/language/openapi.json';

    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->loadFixturesLocal(
            array(
                'Graviton\I18nBundle\DataFixtures\MongoDB\LoadLanguageData',
                'Graviton\I18nBundle\DataFixtures\MongoDB\LoadTranslationLanguageData'
            )
        );
    }

    /**
     * check if a list of all languages can be optained
     *
     * @return void
     */
    public function testFindAll()
    {
        $client = static::createRestClient();
        $client->request('GET', '/i18n/language/');

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseSchemaRel(self::SCHEMA_URL, $response);

        // we assume that initially all systems will only know of the english lang
        $this->assertcount(1, $results);

        $this->assertEquals('en', $results[0]->id);

        $this->assertEquals('en', $response->headers->get('Content-Language'));

        $this->assertEquals('English', $results[0]->name->en);
    }

    /**
     * see if our accept-language header cache works as expected
     *
     * @return void
     */
    public function testLanguageHeaderCaching()
    {
        $client = static::createRestClient();
        $client->request('GET', '/i18n/language/', [], [], array('HTTP_ACCEPT_LANGUAGE' => 'en'));
        $this->assertEquals('en', $client->getResponse()->headers->get('Content-Language'));

        // add a new language
        $newLang = new \stdClass;
        $newLang->id = 'zh';
        $newLang->name = new \stdClass;
        $newLang->name->en = 'Chinese';

        $client = static::createRestClient();
        $client->post('/i18n/language/', $newLang);

        // see if it is in the header
        $client = static::createRestClient();
        $client->request('GET', '/i18n/language/', [], [], array('HTTP_ACCEPT_LANGUAGE' => 'en,de,zh'));
        $this->assertEquals('en, zh', $client->getResponse()->headers->get('Content-Language'));
    }

    /**
     * validate that multiple languages work as advertised
     *
     * @return void
     */
    public function testMultiLangFinding()
    {
        $this->loadFixturesLocal(
            array(
                'Graviton\I18nBundle\DataFixtures\MongoDB\LoadLanguageData',
                'Graviton\I18nBundle\DataFixtures\MongoDB\LoadMultiLanguageData',
            )
        );

        $client = static::createRestClient();
        $client->request('GET', '/i18n/language/');

        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseSchemaRel(self::SCHEMA_URL, $response);

        $this->assertcount(3, $results);

        $this->assertEquals('de, en, fr', $response->headers->get('Content-Language'));

        $this->assertEquals('de', $results[0]->id);
        $this->assertEquals('German', $results[0]->name->en);

        $this->assertEquals('en', $results[1]->id);
        $this->assertEquals('English', $results[1]->name->en);

        $this->assertEquals('fr', $results[2]->id);
        $this->assertEquals('French', $results[2]->name->en);
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
        $client->post('/i18n/language/', $newLang);
        $response = $client->getResponse();

        $client = static::createRestClient();
        $client->request('GET', $response->headers->get('Location'));
        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseSchemaRel(self::SCHEMA_URL, $response);
        $this->assertEquals('de', $results->id);
        $this->assertEquals('de, en', $response->headers->get('Content-Language'));

        $client = static::createRestClient();
        $client->request('GET', '/i18n/language/', [], [], array('HTTP_ACCEPT_LANGUAGE' => 'en,de'));
        $this->assertEquals('de, en', $client->getResponse()->headers->get('Content-Language'));

        $client = static::createRestClient();
        $client->request('GET', '/i18n/language/en', [], [], array('HTTP_ACCEPT_LANGUAGE' => 'en,de'));
        $results = $client->getResults();

        $this->assertEquals('English', $results->name->en);
        $this->assertEquals('Englisch', $results->name->de);
    }

    /**
     * test to add a language and alter a translatable via other service and check
     * if the catalogue gets updated accordingly
     *
     * @return void
     */
    public function testCacheInvalidation()
    {
        $newLang = new \stdClass;
        $newLang->id = 'es';
        $newLang->name = new \stdClass;
        $newLang->name->en = 'Spanish';

        $client = static::createRestClient();
        $client->post('/i18n/language/', $newLang);
        $response = $client->getResponse();

        $client = static::createRestClient();
        $client->request('GET', $response->headers->get('Location'));
        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseSchemaRel(self::SCHEMA_URL, $response);
        $this->assertEquals('es', $results->id);
        $this->assertEquals('en, es', $response->headers->get('Content-Language'));

        // update description for new language
        $putLang = new \stdClass;
        $putLang->id = 'es';
        $putLang->name = new \stdClass;
        $putLang->name->en = 'Spanish';
        $putLang->name->es = 'Español';

        $client = static::createRestClient();
        $client->put('/i18n/language/es', $putLang, [], [], array('HTTP_ACCEPT_LANGUAGE' => 'es'));

        $client = static::createRestClient();
        $client->request(
            'GET',
            '/i18n/language/es',
            [],
            [],
            array('HTTP_ACCEPT_LANGUAGE' => 'es')
        );
        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseSchemaRel(self::SCHEMA_URL, $response);
        $this->assertEquals('es', $results->id);
        $this->assertEquals('Español', $results->name->es);
        $this->assertEquals('en, es', $response->headers->get('Content-Language'));

        // now, do it again to see if subsequent changes get reflected properly (triggerfile check)
        $newPutLang = clone $putLang;
        $newPutLang->name->es = 'Espanyol'; // this is a catalan way to spell 'Spanish'

        $client = static::createRestClient();
        $client->put('/i18n/language/es', $newPutLang, [], [], array('HTTP_ACCEPT_LANGUAGE' => 'es'));

        $client = static::createRestClient();
        $client->request(
            'GET',
            '/i18n/language/es',
            [],
            [],
            array('HTTP_ACCEPT_LANGUAGE' => 'es')
        );
        $response = $client->getResponse();
        $results = $client->getResults();

        $this->assertResponseSchemaRel(self::SCHEMA_URL, $response);
        $this->assertEquals('es', $results->id);
        $this->assertEquals('Espanyol', $results->name->es);
        $this->assertEquals('en, es', $response->headers->get('Content-Language'));
    }

    /**
     * check that we do not return unknown languages
     *
     * @return void
     */
    public function testDontReturnUnknownLanguage()
    {
        $client = static::createRestClient();

        $client->request('GET', '/i18n/language/en', [], [], array('HTTP_ACCEPT_LANGUAGE' => 'en,de'));

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

        $client->request('GET', '/schema/i18n/language/collection', [], [], ['HTTP_ACCEPT_LANGUAGE' => 'en,de']);

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
