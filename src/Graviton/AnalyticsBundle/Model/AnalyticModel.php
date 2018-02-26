<?php
/**
 * Schema Class for output data.
 */
namespace Graviton\AnalyticsBundle\Model;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;

/**
 * Schema
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class AnalyticModel
{
    protected $collection;
    protected $route;
    protected $aggregate;
    protected $pipeline;
    protected $schema;
    protected $type;
    protected $cacheTime;

    /**
     * String collection
     * @return mixed
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Set value of collection
     * @param mixed $collection string name
     * @return void
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

    /**
     * Route path
     * @return mixed
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set path
     * @param mixed $route string route
     * @return void
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }

    /**
     * Mongodb Aggregates
     * @return mixed
     */
    public function getAggregate()
    {
        return $this->aggregate ?: [];
    }

    /**
     * Set mongodb query
     * @param mixed $aggregate object type for query data
     * @return void
     */
    public function setAggregate($aggregate)
    {
        $this->aggregate = $aggregate;
    }

    /**
     * @param mixed $pipeline Data array for query
     * @return void
     */
    public function setPipeline($pipeline)
    {
        $this->pipeline = $pipeline;
    }

    /**
     * Schema for response
     * @return mixed
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Schema data
     * @param mixed $schema object schema
     * @return void
     */
    public function setSchema($schema)
    {
        $this->schema = $schema;
    }

    /**
     * Type of response data
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Type for representation
     * @param mixed $type string view
     * @return void
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Time for this route data to be cached
     * @return mixed
     */
    public function getCacheTime()
    {
        return $this->cacheTime;
    }

    /**
     * Time for this route data to be cached
     * @param integer $cacheTime seconds to be cached
     * @return void
     */
    public function setCacheTime($cacheTime)
    {
        $this->cacheTime = (int) $cacheTime;
    }



    /**
     * Build a output Db Model aggregation pipeline array.
     *
     * @return array
     */
    public function getPipeline()
    {
        $rtnPipeline = [];

        if ($pipeline = $this->pipeline) {
            foreach ($pipeline as $pipe) {
                foreach ($pipe as $op => $query) {
                    $rtnPipeline[] = [
                        $op => $this->parseObjectDates($query)
                    ];
                }
            }
        } elseif ($aggregate = $this->getAggregate()) {
            foreach ($aggregate as $op => $query) {
                $rtnPipeline[] = [
                    $op => $this->parseObjectDates($query)
                ];
            }
        }

        if (empty($rtnPipeline)) {
            throw new  InvalidArgumentException('Wrong configuration for Aggregation pipeline');
        }

        return $rtnPipeline;
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
