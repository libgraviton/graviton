<?php
/**
 * Schema Class for output data.
 */
namespace Graviton\AnalyticsBundle\Model;

/**
 * Schema
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class AnalyticModel
{
    protected $collection;
    protected $route;
    protected $aggregate;
    protected $schema;
    protected $type;

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
        return $this->aggregate;
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
     * Build query pipeline for aggregate mongo
     * @return array
     */
    public function getPipeline()
    {
        $pipeline = [];
        $aggregate = $this->getAggregate();
        if (empty($aggregate)) {
            return $pipeline;
        }

        if (property_exists($aggregate, 'match') && !empty((array) $aggregate->match)) {
            $pipeline[] = [
                '$match' => $this->aggregate->match
            ];
        }

        if (property_exists($aggregate, 'group') && !empty((array) $aggregate->group)) {
            $pipeline[] = [
                '$group' => $this->aggregate->group
            ];
        }

        return $pipeline;
    }
}
