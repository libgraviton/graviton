<?php
/**
 * Schema Class for output data.
 */

namespace Graviton\AnalyticsBundle\Model;

/**
 * Schema
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class AnalyticModel
{

    protected $database;
    protected $collection;
    protected $class;
    protected $route;
    protected $aggregate = [];
    protected $cacheInvalidationCollections = [];
    protected $schema;
    protected $type;
    protected $cacheTime;
    protected $params = [];
    protected $processor;
    protected $useSecondary = false;

    /**
     * get Database
     *
     * @param string $pipelineName which pipeline you want the database for
     *
     * @return string database name name
     */
    public function getDatabase($pipelineName = null)
    {
        if (!is_array($this->database)) {
            return $this->database;
        }

        if (isset($this->database[$pipelineName])) {
            return $this->database[$pipelineName];
        }

        return null;
    }

    /**
     * set Database
     *
     * @param mixed $database database
     *
     * @return void
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }

    /**
     * get Class
     *
     * @param string $pipelineName pipeline name
     *
     * @return mixed Class
     */
    public function getClass($pipelineName = null)
    {
        if (!is_array($this->class)) {
            return $this->class;
        }

        if (isset($this->class[$pipelineName])) {
            return $this->class[$pipelineName];
        }

        return null;
    }

    /**
     * set Class
     *
     * @param mixed $class class
     *
     * @return void
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * String collection
     *
     * @param string $pipelineName which pipeline you want the collection for
     *
     * @return string collection name
     */
    public function getCollection($pipelineName = null)
    {
        if (!is_array($this->collection)) {
            return $this->collection;
        }

        if ($pipelineName == null) {
            throw new \LogicException(
                'If you specify multiple collections in your analytics definition, ' .
                'you must define multiple pipelines and vice versa and name them the same.'
            );
        }

        if (!isset($this->collection[$pipelineName])) {
            throw new \LogicException(
                'No collection defined for pipeline ' . $pipelineName . '. ' .
                'If all pipelines share the same collection, define "collection" attribute as string.'
            );
        }

        return $this->collection[$pipelineName];
    }

    /**
     * gets all collections involved in this analytic
     *
     * @return string[] array of collection names
     */
    public function getAllCollections()
    {
        if (is_array($this->collection)) {
            return $this->collection;
        } elseif (is_null($this->collection)) {
            return [];
        }
        return [$this->collection];
    }

    /**
     * get collections that need to be invalidated in cache
     *
     * @return array|string[]
     */
    public function getCacheInvalidationCollections()
    {
        return array_merge(
            $this->getAllCollections(),
            $this->cacheInvalidationCollections
        );
    }

    /**
     * set collections
     *
     * @param array $collections collections
     * @return void
     */
    public function setCacheInvalidationCollections($collections)
    {
        $this->cacheInvalidationCollections = $collections;
    }

    /**
     * Set value of collection
     *
     * @param mixed $collection string name
     *
     * @return void
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

    /**
     * get Processor
     *
     * @return mixed Processor
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * set Processor
     *
     * @param mixed $processor processor
     *
     * @return void
     */
    public function setProcessor($processor)
    {
        $this->processor = $processor;
    }

    /**
     * if this is a multipipeline
     *
     * @return boolean
     */
    public function getMultipipeline()
    {
        return (is_array($this->collection));
    }

    /**
     * Route path
     *
     * @return mixed
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set path
     *
     * @param mixed $route string route
     *
     * @return void
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }

    /**
     * Set mongodb query
     *
     * @param mixed $aggregate object type for query data
     *
     * @return void
     */
    public function setAggregate($aggregate)
    {
        $this->aggregate = $aggregate;
    }

    /**
     * Schema for response
     *
     * @return mixed
     */
    public function getSchema()
    {
        $schema = $this->schema;
        $schema['x-params'] = $this->getParams();
        return $schema;
    }

    /**
     * Schema data
     *
     * @param mixed $schema object schema
     *
     * @return void
     */
    public function setSchema($schema)
    {
        $this->schema = $schema;
    }

    /**
     * Type of response data
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Type (array or object)
     *
     * @param mixed $type string view
     *
     * @return void
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Time for this route data to be cached
     *
     * @return mixed
     */
    public function getCacheTime()
    {
        return $this->cacheTime;
    }

    /**
     * Time for this route data to be cached
     *
     * @param integer $cacheTime seconds to be cached
     *
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
        if ($this->getMultipipeline()) {
            $pipelines = [];
            foreach ($this->class as $name => $className) {
                if (!class_exists($className)) {
                    throw new \LogicException("Analytics class '" . $className . "' does not exist!");
                }
                $class = new $className();
                $class->setParams($params);
                $pipelines[$name] = $class->get();
            }
            return $pipelines;
        } else {
            $className = $this->class;
            if (!class_exists($className)) {
                throw new \LogicException("Analytics class '" . $className . "' does not exist!");
            }
            $class = new $className();
            $class->setParams($params);
            return $class->get();
        }
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
     * get UseSecondary
     *
     * @return bool UseSecondary
     */
    public function isUseSecondary()
    {
        return $this->useSecondary;
    }

    /**
     * set UseSecondary
     *
     * @param bool $useSecondary useSecondary
     *
     * @return void
     */
    public function setUseSecondary($useSecondary)
    {
        $this->useSecondary = $useSecondary;
    }
}
