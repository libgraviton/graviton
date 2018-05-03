<?php
/**
 * Database manager and query manager
 */

namespace Graviton\AnalyticsBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager;
use Graviton\AnalyticsBundle\Model\AnalyticModel;
use Graviton\AnalyticsBundle\ProcessorInterface;
use Graviton\DocumentBundle\Service\DateConverter;

/**
 * Manager for data layer single responsibility
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class AnalyticsManager
{
    /**
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * @var string
     */
    private $databaseName;

    /**
     * @var DateConverter
     */
    private $dateConverter;

    /**
     * AnalyticsManager constructor.
     * @param DocumentManager $documentManager Db manager and query control
     * @param string          $databaseName    Db string name
     * @param DateConverter   $dateConverter   date converter
     */
    public function __construct(
        DocumentManager $documentManager,
        $databaseName,
        DateConverter $dateConverter
    ) {
        $this->documentManager = $documentManager;
        $this->databaseName = $databaseName;
        $this->dateConverter = $dateConverter;
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
        // Build aggregation pipeline
        $pipeline = $model->getAggregate($params);

        $conn = $this->documentManager->getConnection();
        // all data will be here first..
        $data = [];

        if (!$model->getMultipipeline()) {
            $collection = $conn->selectCollection($this->databaseName, $model->getCollection());
            $data[] = $collection->aggregate($pipeline, ['cursor' => true])->toArray();
        } else {
            foreach ($pipeline as $pipelineName => $definition) {
                $collection = $conn->selectCollection($this->databaseName, $model->getCollection($pipelineName));
                $data[$pipelineName] = $collection->aggregate($definition, ['cursor' => true])->toArray();
            }
        }

        /*** PROCESSING HERE ***/
        $processor = $model->getProcessor();
        if (!is_null($processor)) {
            if (!class_exists($processor)) {
                throw new \LogicException('Defined processor class '.$processor.' does not exist');
            }

            $processorClass = new $processor();
            if (!$processorClass instanceof ProcessorInterface) {
                throw new \LogicException('Processor class '.$processor.' does not implement ProcessorInterface.');
            }

            $data = $processorClass->process($data, $params);
        }

        // process dates
        $data = $this->convertDates($data);

        if (!$model->getMultipipeline()) {
            $data = reset($data);
            if ('object' === $model->getType()) {
                if (isset($data[0])) {
                    $data = $data[0];
                } else {
                    $data = new \stdClass();
                }
            }
        }

        return $data;
    }

    /**
     * convert date representations in the returned output.. as we are not typed here, we don't
     * know what to expect..
     *
     * @param array $data data
     *
     * @return array data with formatted dates
     */
    private function convertDates(array $data = null)
    {
        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $val) {
            if (is_array($val)) {
                $data[$key] = $this->convertDates($val);
            }
            if ($val instanceof \MongoDate) {
                $data[$key] = $this->dateConverter->formatDateTime($val->toDateTime());
            }
        }
        return $data;
    }
}
