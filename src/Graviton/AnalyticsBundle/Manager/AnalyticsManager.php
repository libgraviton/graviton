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

        $pipeline = [];
        // Json Definition object key -> value to array object.
        foreach ($schema->getAggregate() as $op => $query) {
            $pipeline[] = [
                $op => (array) $query
            ];
        }

        $iterator = $collection->aggregate($pipeline);
        if ('object' === $schema->getType()) {
            return $iterator->getSingleResult();
        }
        return $iterator->toArray();
    }
}
