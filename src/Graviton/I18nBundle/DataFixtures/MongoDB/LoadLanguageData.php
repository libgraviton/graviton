<?php
/**
 * /core/app fixtures for mongodb app collection.
 */

namespace Graviton\I18nBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Graviton\I18nBundle\Document\Language;

/**
 * Load Language data fixtures into mongodb
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class LoadLanguageData implements FixtureInterface
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
        $lang = new Language;
        $lang->setId('en');
        $lang->setName('English');
        $manager->persist($lang);

        $lang = new Language;
        $lang->setId('de');
        $lang->setName('German');
        $manager->persist($lang);

        $lang = new Language;
        $lang->setId('fr');
        $lang->setName('French');
        $manager->persist($lang);

        $manager->flush();
    }
}
