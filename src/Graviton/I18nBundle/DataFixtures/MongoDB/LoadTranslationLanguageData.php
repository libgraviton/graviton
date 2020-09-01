<?php
/**
 * /core/app fixtures for mongodb app collection.
 */

namespace Graviton\I18nBundle\DataFixtures\MongoDB;

use Graviton\MongoDB\Fixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Graviton\I18nBundle\Document\Translation;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class LoadTranslationLanguageData implements FixtureInterface
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
                'original' => 'German',
                'localized' => 'Deutsch'
            ],
            [
                'language' => 'de',
                'original' => 'English',
                'localized' => 'Englisch'
            ],
            [
                'language' => 'fr',
                'original' => 'English',
                'localized' => 'Anglais'
            ],
            [
                'language' => 'es',
                'original' => 'English',
                'localized' => 'Inglés'
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
