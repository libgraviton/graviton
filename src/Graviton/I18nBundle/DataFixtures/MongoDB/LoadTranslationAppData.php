<?php
/**
 * /core/app fixtures for mongodb translatables collection.
 */

namespace Graviton\I18nBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Graviton\I18nBundle\Document\Translation;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class LoadTranslationAppData implements FixtureInterface
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
        $data = [
            [
                'language' => 'de',
                'original' => 'Administration',
                'localized' => 'Die Administration'
            ],
            [
                'language' => 'de',
                'original' => 'Tablet',
                'localized' => 'Tablet'
            ]
        ];

        foreach ($data as $record) {
            $translation = new Translation();
            $translation->setLanguage($record['language']);
            $translation->setOriginal($record['original']);
            $translation->setLocalized($record['localized']);
            $manager->persist($translation);
            $manager->flush();
        }
    }
}
