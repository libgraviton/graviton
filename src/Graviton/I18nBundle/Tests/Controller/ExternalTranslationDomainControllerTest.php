<?php
/**
 * Functional test for cache handling for non-existent translatable domains on Container build time.
 * On Container build time, only existent Translatable domains are added to the Translator map.
 * So they will not be loaded until the Container is rebuild. We fixed that by creating our own
 * Translator and I18nCacheUtils. This test ensures that this works as expected for the user.
 *
 * IMPORTANT: In order for this test to stay relevant, the service definition for
 * /external/translatable MUST NOT contain ANY fixtures as this would produce the domain at Container
 * build time!
 */

namespace Graviton\I18nBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ExternalTranslationDomainControllerTest extends RestTestCase
{

    /**
     * setup function
     *
     * @return void
     */
    public function setUp()
    {
        $this->loadFixtures(
            array(
                'Graviton\I18nBundle\DataFixtures\MongoDB\LoadLanguageData',
                'Graviton\I18nBundle\DataFixtures\MongoDB\LoadMultiLanguageData',
                'Graviton\I18nBundle\DataFixtures\MongoDB\LoadTranslatableData',
            ),
            null,
            'doctrine_mongodb'
        );

        // make sure we have no resource files for domain 'external'
        $fs = new Filesystem();
        $finder = new Finder();
        $finder
            ->files()
            ->in(__DIR__.'/../../Resources/translations')
            ->name('external.*.*');

        foreach ($finder as $file) {
            $fs->remove($file->getRealPath());
        }
    }

    /**
     * see if a non existent domain gets translated correctly.
     * it must invalidate the cache and trigger a reload of the translations in order for this to work.
     *
     * @return void
     */
    public function testCacheUtils()
    {
        $resource = new \stdClass;
        $resource->id = 'test';
        $resource->myString = new \stdClass;
        $resource->myString->en = 'The John';
        $resource->myString->de = 'Der Hans';
        $resource->myString->fr = 'Le Jean';

        $client = static::createRestClient();
        $client->put(
            '/external/translatable/test',
            $resource,
            array(),
            array(),
            array('HTTP_ACCEPT_LANGUAGE' => 'en,de,fr')
        );

        $this->assertEquals(204, $client->getResponse()->getStatusCode());

        $client = static::createRestClient();
        $client->request(
            'GET',
            '/external/translatable/test',
            array(),
            array(),
            array('HTTP_ACCEPT_LANGUAGE' => 'en,de,fr')
        );

        $results = $client->getResults();

        $this->assertEquals($resource->id, $results->id);
        $this->assertEquals($resource->myString->en, $results->myString->en);
        $this->assertEquals($resource->myString->de, $results->myString->de);
        $this->assertEquals($resource->myString->fr, $results->myString->fr);
    }
}
