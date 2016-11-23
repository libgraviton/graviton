<?php
/**
 * Index migration
 */

namespace Graviton\I18nBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Doctrine\MongoDB\Database;
use Graviton\I18nBundle\Document\Translatable;
use MongoDB;
use MongoCollection;

/**
 * Migrate domain_1_locale_1_original_1 index
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class Version20161122111410 extends AbstractMigration
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
        return 'Build id for the new sha1 implementation';
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
        $collection = $db->createCollection($this->collection);

        // Remove index
        $collection->deleteIndex(['domain_1_locale_1_original_1']);

        /** @var array $translatable */
        foreach ($collection->find() as $translatable) {
            $id = $translatable['_id'];
            if (!ctype_xdigit($id)) {
                $newId = sha1($id);
                if ($collection->findOne(['_id' => $newId], ['id'])) {
                    $collection->remove(['_id' => $id]);
                } else {
                    $translatable['_id'] = $newId;
                    $collection->insert($translatable);
                    $collection->remove(['_id' => $id]);
                }
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
