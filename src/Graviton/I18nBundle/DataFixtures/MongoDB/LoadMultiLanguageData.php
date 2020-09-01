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
class LoadMultiLanguageData implements FixtureInterface
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
        foreach (['de' => 'German', 'fr' => 'French'] as $id => $name) {
            $lang = new Language;
            $lang->setId($id);
            $lang->setName(Translatable::createFromOriginalString($name));
            $manager->persist($lang);
        }

        $manager->flush();
    }
}
