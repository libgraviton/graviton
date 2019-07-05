<?php
/**
 * Basic functional test for our simple translator
 */

namespace Graviton\I18nBundle\Tests\Integration;

use Doctrine\Common\Cache\ArrayCache;
use Graviton\DocumentBundle\Entity\Translatable;
use Graviton\I18nBundle\DataFixtures\MongoDB\LoadLanguageData;
use Graviton\I18nBundle\DataFixtures\MongoDB\LoadMultiLanguageData;
use Graviton\I18nBundle\DataFixtures\MongoDB\LoadTranslationData;
use Graviton\I18nBundle\Translator\Translator;
use Graviton\TestBundle\Test\GravitonTestCase;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class TranslatorTest extends GravitonTestCase
{

    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->loadFixturesLocal(
            array(
                LoadLanguageData::class,
                LoadMultiLanguageData::class,
                LoadTranslationData::class
            )
        );
    }

    /**
     * Test basic translation function
     *
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     *
     * @return void
     */
    public function testTranslation()
    {
        $manager = $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager');
        $translator = new Translator(
            $manager,
            'en',
            new ArrayCache(),
            new ArrayCache(),
            3
        );

        $translation = $translator->translate('English');

        $this->assertEquals('English', $translation['en']);
        $this->assertEquals('Englisch', $translation['de']);
        $this->assertEquals('Anglais', $translation['fr']);

        // change some
        $newtrans = Translatable::createFromTranslations(
            [
                'en' => 'English',
                'de' => 'Sö inglisch'
            ]
        );
        $translator->persistTranslatable($newtrans);

        $translation = $translator->translate('English');

        $this->assertEquals('English', $translation['en']);
        $this->assertEquals('Sö inglisch', $translation['de']);
        $this->assertEquals('Anglais', $translation['fr']);
    }
}
