<?php
/**
 * /core/app fixtures for mongodb app collection.
 */

namespace Graviton\I18nBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Graviton\I18nBundle\Document\Translatable;

/**
 * Load Translatable data fixtures into mongodb
 *
 * @category I18nBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/MIT MIT License (c) 2015 Swisscom
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

        $manager->persist($deEnglish);
        $manager->flush();
    }
}
