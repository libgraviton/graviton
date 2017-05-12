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

    /** @var string */
    protected $databaseName;

    /**
     * AnalyticsManager constructor.
     * @param DocumentManager $documentManager Db manager and query control
     * @param string          $databaseName    Db string name
     */
    public function __construct(
        DocumentManager $documentManager,
        $databaseName
    ) {
        $this->documentManager = $documentManager;
        $this->databaseName = $databaseName;
    }

    /**
     * Query db based on definition
     * Another option is to use: $collection->createAggregationBuilder();
     *
     * @param AnalyticModel $model Definition
     * @return array|object
     */
    public function getData(AnalyticModel $model)
    {
        $conn = $this->documentManager->getConnection();
        $collection = $conn->selectCollection($this->databaseName, $model->getCollection());

        // Build aggregation pipeline
        $pipeline = $model->getPipeline();

        $iterator = $collection->aggregate($pipeline);
        if ('object' === $model->getType()) {
            return $iterator->getSingleResult();
        }
        return $iterator->toArray();
    }
}
