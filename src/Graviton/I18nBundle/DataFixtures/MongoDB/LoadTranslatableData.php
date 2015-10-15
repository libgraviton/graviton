<?php
/**
 * /core/app fixtures for mongodb app collection.
 */

namespace Graviton\I18nBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Graviton\DocumentBundle\Entity\ExtReference;
use Graviton\I18nBundle\Document\Translatable;
use Graviton\I18nBundle\Document\TranslatableLanguage;

/**
 * Load Translatable data fixtures into mongodb
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class LoadTranslatableData implements FixtureInterface
{
    /**
     * {@inheritDoc}
     *
     * @param ObjectManager $manager Object Manager
     *
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        $deGerman = new Translatable;
        $deGerman->setId('i18n-de-German');
        $deGerman->setDomain('i18n');
        $deGerman->setLocale('de');
        $deGerman->setOriginal('German');
        $deGerman->setTranslated('Deutsch');
        $deGerman->setIsLocalized(true);

        $manager->persist($deGerman);

        $deEnglish = new Translatable;
        $deEnglish->setId('i18n-de-English');
        $deEnglish->setDomain('i18n');
        $deEnglish->setLocale('de');
        $deEnglish->setOriginal('English');
        $deEnglish->setTranslated('Englisch');
        $deEnglish->setIsLocalized(true);
        $language = new TranslatableLanguage;
        $language->setRef(ExtReference::create('Language', 'de'));
        $deEnglish->setLanguage($language);

        $manager->persist($deEnglish);
        $manager->flush();
    }
}
