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
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
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
        $deGerman->setId('messages-de-German');
        $deGerman->setDomain('messages');
        $deGerman->setLocale('de');
        $deGerman->setOriginal('German');
        $deGerman->setTranslated('Deutsch');
        $deGerman->setIsLocalized(true);

        $manager->persist($deGerman);

        $deEnglish = new Translatable;
        $deEnglish->setId('messages-de-English');
        $deEnglish->setDomain('messages');
        $deEnglish->setLocale('de');
        $deEnglish->setOriginal('English');
        $deEnglish->setTranslated('Englisch');
        $deEnglish->setIsLocalized(true);

        $manager->persist($deEnglish);
        $manager->flush();
    }
}
