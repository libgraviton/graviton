<?php
/**
 * Index migration
 */

namespace Graviton\I18nBundle\Migrations\MongoDB;

use AntiMattr\MongoDB\Migrations\AbstractMigration;
use Doctrine\MongoDB\Database;
use MongoDB;
use MongoCollection;

/**
 * Migrate domain_1_locale_1_original_1 index
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class Version20160303110805 extends AbstractMigration
{
    /**
     * @var string
     */
    private $collection = 'Translatable';

    /**
     * @var array
     */
    private $index = ['domain' => 1, 'locale' => 1, 'original' => 1];

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'drop the uniqueness of the domain_1_locale_1_original_1 index';
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
        $db->createCollection($this->collection)->deleteIndex($this->index);
        $db->createCollection($this->collection)->ensureIndex($this->index);
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
        $db->createCollection($this->collection)->deleteIndex($this->index);
        $db->createCollection($this->collection)->ensureIndex($this->index, ['unique' => true]);
    }
}
