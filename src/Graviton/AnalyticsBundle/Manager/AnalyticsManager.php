<?php
/**
 * Database manager and query manager
 */

namespace Graviton\AnalyticsBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager;
use Graviton\AnalyticsBundle\Model\AnalyticModel;

/**
 * Manager for data layer single responsibility
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class AnalyticsManager
{
    /** @var DocumentManager */
    protected $documentManager;

    /**
     * AnalyticsManager constructor.
     * @param DocumentManager $documentManager Db manager and query control
     */
    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     * Query db based on definition
     * Another option is to use: $collection->createAggregationBuilder();
     *
     * @param AnalyticModel $schema Definition
     * @return array|object
     */
    public function getData(AnalyticModel $schema)
    {
        $conn = $this->documentManager->getConnection();
        $db = $this->documentManager->getConfiguration()->getDefaultDB();
        $collection = $conn->selectCollection($db, $schema->getCollection());

        $pipeline = $schema->getPipeline();
        $data = $collection->aggregate($pipeline)->toArray();
        if ('object' === $schema->getType()) {
            return array_key_exists(0, $data) ? $data[0] : new \stdClass();
        }
        return $data;
    }
}
