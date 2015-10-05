<?php
/**
 * /core/app fixtures for mongodb translatables collection.
 */

namespace Graviton\I18nBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Graviton\DocumentBundle\Entity\ExtReference;
use Graviton\I18nBundle\Document\Translatable;
use Graviton\I18nBundle\Document\TranslatableLanguage;

/**
 * Load Translatable data fixtures (for /core/app) into mongodb
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class LoadTranslatablesApp implements FixtureInterface
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
        $deAdmin = new Translatable;
        $deAdmin->setId('i18n-de-Administration');
        $deAdmin->setDomain('core');
        $deAdmin->setLocale('de');
        $deAdmin->setOriginal('Administration');
        $deAdmin->setTranslated('Die Administration');
        $deAdmin->setIsLocalized(true);
        $language = new TranslatableLanguage;
        $language->setRef(ExtReference::create('Language', 'de'));
        $deAdmin->setLanguage($language);
        $manager->persist($deAdmin);

        $frAdmin = new Translatable;
        $frAdmin->setId('i18n-fr-Administration');
        $frAdmin->setDomain('core');
        $frAdmin->setLocale('fr');
        $frAdmin->setOriginal('Administration');
        $frAdmin->setIsLocalized(false);
        $language = new TranslatableLanguage;
        $language->setRef(ExtReference::create('Language', 'fr'));
        $frAdmin->setLanguage($language);
        $manager->persist($frAdmin);

        $enAdmin = new Translatable;
        $enAdmin->setId('i18n-en-Administration');
        $enAdmin->setDomain('core');
        $enAdmin->setLocale('en');
        $enAdmin->setOriginal('Administration');
        $enAdmin->setTranslated('Administration');
        $enAdmin->setIsLocalized(true);
        $language = new TranslatableLanguage;
        $language->setRef(ExtReference::create('Language', 'en'));
        $enAdmin->setLanguage($language);
        $manager->persist($enAdmin);

        /*** THIS TRANSLATABLES HERE SHOULD *NOT* BE TRANSLATED FOR TESTS ***/

        $deTablet = new Translatable;
        $deTablet->setId('i18n-de-Tablet');
        $deTablet->setDomain('core');
        $deTablet->setLocale('de');
        $deTablet->setOriginal('Tablet');
        $deTablet->setIsLocalized(false);
        $language = new TranslatableLanguage;
        $language->setRef(ExtReference::create('Language', 'de'));
        $deTablet->setLanguage($language);
        $manager->persist($deTablet);

        $frTablet = new Translatable;
        $frTablet->setId('i18n-fr-Tablet');
        $frTablet->setDomain('core');
        $frTablet->setLocale('fr');
        $frTablet->setOriginal('Tablet');
        $frTablet->setIsLocalized(false);
        $language = new TranslatableLanguage;
        $language->setRef(ExtReference::create('Language', 'fr'));
        $frTablet->setLanguage($language);
        $manager->persist($frTablet);

        $enTablet = new Translatable;
        $enTablet->setId('i18n-en-Tablet');
        $enTablet->setDomain('core');
        $enTablet->setLocale('en');
        $enTablet->setOriginal('Tablet');
        $enTablet->setTranslated('Tablet');
        $enTablet->setIsLocalized(true);
        $language = new TranslatableLanguage;
        $language->setRef(ExtReference::create('Language', 'en'));
        $enTablet->setLanguage($language);
        $manager->persist($enTablet);

        $manager->flush();
    }
}
