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
                $op => $this->parseObjectDates($query)
            ];
        }

        $iterator = $collection->aggregate($pipeline);
        if ('object' === $schema->getType()) {
            return $iterator->getSingleResult();
        }
        return $iterator->toArray();
    }

    /**
     * Enabling to possibility to create dtae queries
     * Will replace PARSE_DATE(date|format)
     * sample: PARSE_DATE(-4 years|Y) -> new DateTime(-4 years)->format(Y) -> 2013
     *
     * @param object $query Aggregation query
     * @return object
     */
    private function parseObjectDates($query)
    {
        $string = json_encode($query);
        preg_match_all('/PARSE_DATE\(([^\)]+)\)/', $string, $matches);
        if ($matches && array_key_exists(1, $matches) && is_array($matches[1])) {
            foreach ($matches[0] as $key => $value) {
                $formatting = explode('|', $matches[1][$key]);
                $date = new \DateTime($formatting[0]);
                $string = str_replace('"'.$value.'"', $date->format($formatting[1]), $string);
            }
            $query = json_decode($string);
        }
        return $query;
    }
}
