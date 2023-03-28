<?php
/**
 * Translatable -> Translation migration
 */

namespace Graviton\I18nBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use MongoDB\Database;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class Version20181008102600 extends AbstractMigration
{
    /**
     * @var string
     */
    private $collection = 'Translatable';

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Migrate Translatable to Translation';
    }

    /**
     * recreate index without unique flag
     *
     * @param Database $db database to migrate
     *
     * @return void
     */
    public function up(Database $db)
    {
        $collection = $db->selectCollection($this->collection);
        $targetCollection = $db->selectCollection('Translation');

        $aggregate = [
            [
                '$match' => [
                    'isLocalized' => true,
                    'locale' => ['$ne' => 'en'],
                    'translated' => ['$ne' => null]
                ]
            ],
            [
                '$group' => [
                    '_id' => ['original' => '$original', 'locale' => '$locale'],
                    'translated' => ['$addToSet' => '$translated'],
                    'count' => ['$sum' => 1]
                ]
            ]
        ];

        foreach ($collection->aggregate($aggregate, ['cursor' => true]) as $translatable) {
            $original = $translatable['_id']['original'];
            $language = $translatable['_id']['locale'];
            $translation = $translatable['translated'][0];

            $rec = [
                'original' => $original,
                'language' => $language,
                'localized' => $translation
            ];

            try {
                $targetCollection->insertOne($rec);
            } catch (\Exception $e) {
                echo "Skipping already existing translation of original '{$original}'".PHP_EOL;
            }
        }
    }

    /**
     * re-add unique flag to index
     *
     * @param Database $db database to migrate
     *
     * @return void
     */
    public function down(Database $db)
    {
    }
}
