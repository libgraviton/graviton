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
    protected $aggregate = [];
    protected $schema;
    protected $type;
    protected $cacheTime;
    protected $params = [];

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
        $schema = $this->schema;
        $schema->{'x-params'} = $this->getParams();
        return $schema;
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
     * Type (array or object)
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
     * @param array $params params
     *
     * @return array the pipeline
     */
    public function getAggregate($params = [])
    {
        $aggregate = $this->getParameterizedAggregate($params);

        if (empty($aggregate)) {
            throw new InvalidArgumentException('Wrong configuration for Aggregation pipeline - it is empty!');
        }

        return $aggregate;
    }

    /**
     * get Params
     *
     * @return mixed Params
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * set Params
     *
     * @param mixed $params params
     *
     * @return void
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * returns the pipeline with param values replaced
     *
     * @param array $params the params
     *
     * @return array the pipeline with values filled in
     */
    private function getParameterizedAggregate(array $params)
    {
        $encoded = json_encode($this->aggregate);

        // are there any params?
        if (is_array($params) && !empty($params)) {
            foreach ($params as $name => $value) {
                if (!is_array($value)) {
                    // replace single standalone values in json
                    if (is_int($value) || is_bool($value)) {
                        $encoded = preg_replace('/"\$\{'.$name.'\}"/', $value, $encoded);
                    }
                    // the balance
                    $encoded = preg_replace('/\$\{'.$name.'\}/', $value, $encoded);
                } else {
                    $encoded = preg_replace('/"\$\{'.$name.'\}"/', json_encode($value), $encoded);
                }
            }
        }

        return $this->parseObjectInstances(json_decode($encoded, true));
    }

    /**
     * parse object structures that need to be injected in order to execute the query (like MongoDates or Ids)
     *
     * @param array $struct the pipeline
     *
     * @return array changed pipeline
     */
    private function parseObjectInstances(array $struct)
    {
        foreach ($struct as $key => $prop) {
            if (is_array($prop)) {
                $struct[$key] = $this->parseObjectInstances($prop);
            }
            if (is_string($prop) && $prop == '#newDate#') {
                $struct[$key] = new \MongoDate();
            }
        }
        return $struct;
    }
}
