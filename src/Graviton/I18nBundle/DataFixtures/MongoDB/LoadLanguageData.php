<?php
/**
 * /core/app fixtures for mongodb app collection.
 */

namespace Graviton\I18nBundle\DataFixtures\MongoDB;

use Graviton\MongoDB\Fixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Graviton\DocumentBundle\Entity\Translatable;
use Graviton\I18nBundle\Document\Language;

/**
 * Load Language data fixtures into mongodb
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
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
        $enTag = new Language;
        $enTag->setId('en');
        $enTag->setName(Translatable::createFromOriginalString('English'));

        $manager->persist($enTag);
        $manager->flush();
    }
}
