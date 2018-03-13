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
 * @license  https://opensource.org/licenses/MIT MIT License
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
     * @param AnalyticModel $model  Definition
     * @param array         $params data params
     *
     * @return array|object
     */
    public function getData(AnalyticModel $model, $params = [])
    {
        $conn = $this->documentManager->getConnection();
        $collection = $conn->selectCollection($this->databaseName, $model->getCollection());

        // Build aggregation pipeline
        $pipeline = $model->getAggregate($params);

        $iterator = $collection->aggregate($pipeline, ['cursor' => true]);
        if ('object' === $model->getType()) {
            return $iterator->getSingleResult();
        }
        return $iterator->toArray();
    }
}
