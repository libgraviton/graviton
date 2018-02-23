<?php
/**
 * Testing case
 */
namespace Graviton\AnalyticsBundle\Tests\Model;

use Graviton\AnalyticsBundle\Helper\JsonMapper;
use Graviton\AnalyticsBundle\Model\AnalyticModel;
use Graviton\TestBundle\Test\RestTestCase;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class AnalyticModelTest extends RestTestCase
{
    /**
     * Testing AnalyticsModel::parseObjectDates()
     * Testing AnalyticsModel::getPipeline()
     * Testing JsonMapper::map()
     *
     * @return void
     */
    public function testBuildPipeline()
    {
        $mapper = new JsonMapper();
        $date = new \DateTime('-4 years');
        $year = $date->format('Y');
        $expect = '[{"$match":{"created_year":{"$gte":'.$year.'}}},{"$group":{"_id":"app-count","count":{"$sum":1}}}]';

        $definitionA = json_decode(
            '{
              "collection": "App",
              "route": "app",
              "type": "object",
              "aggregate": {
                "$match": {
                  "created_year": {
                    "$gte": "PARSE_DATE(-4 years|Y)"
                  }
                },
                "$group": {
                  "_id": "app-count",
                  "count": {
                    "$sum": 1
                  }
                }
              },
              "schema": {}
            }
        '
        );

        /** @var AnalyticModel $modelA */
        $modelA = $mapper->map($definitionA, new AnalyticModel());
        $resultA = json_encode($modelA->getPipeline());
        $this->assertEquals($expect, $resultA);

        // Pipeline
        $definitionB = json_decode(
            '{
              "collection": "App",
              "route": "app",
              "type": "object",
              "pipeline": [
                {
                  "$match": {
                    "created_year": {
                      "$gte": "PARSE_DATE(-4 years|Y)"
                    }
                  }
                },
                {
                  "$group": {
                    "_id": "app-count",
                    "count": {
                      "$sum": 1
                    }
                  }
                }
              ],
              "schema": {}
            }
        '
        );

        /** @var AnalyticModel $modelB */
        $modelB = $mapper->map($definitionB, new AnalyticModel());
        $resultB = json_encode($modelB->getPipeline());
        $this->assertEquals($expect, $resultB);

        $this->assertEquals($definitionB->collection, $modelB->getCollection());
        $this->assertEquals($definitionB->route, $modelB->getRoute());
        $this->assertEquals($definitionB->type, $modelB->getType());
        $this->assertEquals($definitionB->schema, $modelB->getSchema());
    }
}
