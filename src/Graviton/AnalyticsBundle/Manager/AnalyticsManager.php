<?php
/**
 * Database manager and query manager
 */

namespace Graviton\AnalyticsBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager;
use Graviton\AnalyticsBundle\Event\PreAggregateEvent;
use Graviton\AnalyticsBundle\Model\AnalyticModel;
use Graviton\AnalyticsBundle\ProcessorInterface;
use Graviton\DocumentBundle\Service\DateConverter;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\ReadPreference;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Manager for data layer single responsibility
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
readonly class AnalyticsManager
{

    /**
     * AnalyticsManager constructor.
     * @param DocumentManager          $documentManager  Db manager and query control
     * @param string                   $databaseName     Db string name
     * @param DateConverter            $dateConverter    date converter
     * @param EventDispatcherInterface $eventDispatcher  event dispatcher
     * @param array                    $aggregateOptions options
     */
    public function __construct(
        private DocumentManager $documentManager,
        private string $databaseName,
        private DateConverter $dateConverter,
        private EventDispatcherInterface $eventDispatcher,
        private array $aggregateOptions = [
            'cursor' => [
                'batchSize' => 100
            ],
            'allowDiskUse' => true
        ]
    ) {
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

        // all data will be here first..
        $data = [];

        // check for readpreference
        $readPreference = new ReadPreference(ReadPreference::RP_PRIMARY);
        if ($model->isUseSecondary()) {
            $readPreference = new ReadPreference(ReadPreference::RP_SECONDARY_PREFERRED);
        }
        $aggregateOptions = array_merge(
            $this->aggregateOptions,
            ['readPreference' => $readPreference]
        );

        if (!$model->getMultipipeline()) {
            $dbName = $model->getDatabase();
            if (is_null($dbName)) {
                $dbName = $this->databaseName;
            }
            $collection = $this->documentManager->getClient()->selectCollection($dbName, $model->getCollection());
            $pipeline = $this->executePreAggregateEvent($pipeline);
            $data[] = $collection->aggregate($pipeline, $aggregateOptions)->toArray();
        } else {
            foreach ($pipeline as $pipelineName => $definition) {
                $dbName = $model->getDatabase($pipelineName);
                if (is_null($dbName)) {
                    $dbName = $this->databaseName;
                }
                $collection = $this->documentManager->getClient()->selectCollection(
                    $dbName,
                    $model->getCollection($pipelineName)
                );
                $definition = $this->executePreAggregateEvent($definition);
                $data[$pipelineName] = $collection->aggregate($definition, $aggregateOptions)->toArray();
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
        $data = $this->convertData($data);

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
     * dispatches our pre aggregate array
     *
     * @param array $pipeline pipeline
     *
     * @return array pipeline
     */
    private function executePreAggregateEvent(array $pipeline)
    {
        $event = new PreAggregateEvent();
        $event->setPipeline($pipeline);
        $event = $this->eventDispatcher->dispatch($event, PreAggregateEvent::NAME);
        return $event->getPipeline();
    }

    /**
     * convert various things in the data that should be rendered differently
     *
     * @param array $data data
     *
     * @return array data with changed things
     */
    private function convertData(array $data = null)
    {
        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $val) {
            /** convert dbrefs */
            if (is_array($val) && isset($val['$ref']) && isset($val['$id'])) {
                $data[$key] = $this->convertData(
                    $this->resolveObject($val['$ref'], $val['$id'])
                );
            } elseif (is_array($val)) {
                $data[$key] = $this->convertData($val);
            }
            /** convert mongodate to text dates **/
            if ($val instanceof UTCDateTime) {
                $data[$key] = $this->dateConverter->formatDateTime($val->toDateTime());
            }
            /** convert mongoid */
            if ($val instanceof ObjectId) {
                $data[$key] = (string) $val;
            }
        }

        return $data;
    }

    /**
     * resolves a dbref array into the actual object
     *
     * @param string $collection collection name
     * @param string $id         record id
     *
     * @return array|null record as array or null
     */
    private function resolveObject($collection, $id)
    {
        return $this->documentManager->getClient()->selectCollection(
            $this->databaseName,
            $collection
        )->findOne(['_id' => $id]);
    }
}
