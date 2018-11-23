<?php
/**
 * Schema Class for output data.
 */
namespace Graviton\AnalyticsBundle\Model;

use Graviton\DocumentBundle\Service\DateConverter;
use GravitonEvojaBasicBundle\Pipeline\CustomerleadsLead;
use GravitonEvojaBasicBundle\Pipeline\CustomerleadsTask;
use Rs\Json\Patch;
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
    /**
     * @var DateConverter
     */
    protected $dateConverter;

    protected $database;
    protected $collection;
    protected $route;
    protected $aggregate = [];
    protected $schema;
    protected $type;
    protected $cacheTime;
    protected $params = [];
    protected $multiPipeline = false;
    protected $processor;

    /**
     * set DateConverter
     *
     * @param DateConverter $dateConverter dateConverter
     *
     * @return void
     */
    public function setDateConverter($dateConverter)
    {
        $this->dateConverter = $dateConverter;
    }

    /**
     * get Database
     *
     * @param string $pipelineName which pipeline you want the database for
     *
     * @return string database name name
     */
    public function getDatabase($pipelineName = null)
    {
        if (!is_object($this->database)) {
            return $this->database;
        }

        if (isset($this->database->$pipelineName)) {
            return $this->database->$pipelineName;
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
     * String collection
     *
     * @param string $pipelineName which pipeline you want the collection for
     *
     * @return string collection name
     */
    public function getCollection($pipelineName = null)
    {
        if (!is_object($this->collection)) {
            return $this->collection;
        }

        if ($pipelineName == null) {
            throw new \LogicException(
                'If you specify multiple collections in your analytics definition, '.
                'you must define multiple pipelines and vice versa and name them the same.'
            );
        }

        if (!property_exists($this->collection, $pipelineName)) {
            throw new \LogicException(
                'No collection defined for pipeline '.$pipelineName.'. '.
                'If all pipelines share the same collection, define "collection" attribute as string.'
            );
        }

        return $this->collection->$pipelineName;
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
        return $this->multiPipeline;
    }

    /**
     * set if this is a multipipeline
     *
     * @param boolean $multiPipeline multi pipeline
     *
     * @return boolean
     */
    public function setMultipipeline($multiPipeline)
    {
        $this->multiPipeline = $multiPipeline;
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

    	$pipeline = new CustomerleadsLead();
    	return $pipeline->get();

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
        // remove nodes (when specified) if optional values are not there..
        $encoded = $this->removeOptionalValueNodes(
            json_encode($this->aggregate),
            $params
        );

        // are there any params?
        if (is_array($params) && !empty($params)) {
            foreach ($params as $name => $value) {
                if (!is_array($value)) {
                    // replace single standalone values in json
                    if (is_int($value) || is_bool($value)) {
                        $encoded = preg_replace('/"\$\{'.$name.'\}"/', $value, $encoded);
                    }

                    // for security (escaping from aggregate), we have to encode this as well, but remove
                    // wrapping quotes
                    $value = substr(json_encode($value), 1, -1);

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
            // simple mongoregex
            if (is_string($prop) && strpos($prop, '#mongoRegex(') !== false) {
                // get value
                preg_match('/#mongoRegex\((.*)\)#/', $prop, $matches);

                if (!isset($matches[1])) {
                    throw new \LogicException('Unable to parse mongoRegex value for property '.$key);
                }
                $struct[$key] = new \MongoRegex('/'.$matches[1].'/i');
            }
            // simple mongodate
            if (is_string($prop) && strpos($prop, '#mongoDate(') !== false) {
                if (!$this->dateConverter instanceof DateConverter) {
                    throw new \LogicException('No DateConverter set on '.__CLASS__.' instance.');
                }

                // get value
                preg_match('/#mongoDate\((.*)\)#/', $prop, $matches);

                if (!isset($matches[1])) {
                    throw new \LogicException('Unable to parse mongoDate value for property '.$key);
                }

                $dateTime = $this->dateConverter->getDateTimeFromString($matches[1]);

                if (!$dateTime instanceof \DateTime) {
                    throw new \LogicException(
                        'Unable to parse value "'.$matches[1].'" into a DateTime instance'
                    );
                }

                $struct[$key] = new \MongoDate($dateTime->format('U'));
            }
        }
        return $struct;
    }

    /**
     * in the 'params' definition, one can define if some nodes of the pipeline should be
     * removed if the param is empty.. here we remove those nodes.. follows phppatch syntax
     *
     * @param string $encodedPipeline pipeline as json string
     * @param array  $params          supplied params
     *
     * @return string changed json
     */
    private function removeOptionalValueNodes($encodedPipeline, array $params)
    {
        $pathsToRemove = [];

        foreach ($this->params as $param) {
            if (isset($param->removeOnAbstinence)) {
                $paramName = $param->name;
                $removals = $param->removeOnAbstinence;

                if ($this->getMultipipeline() && !is_object($removals)) {
                    throw new \LogicException(
                        'In a multipipeline, "removeOnAbstinence" param parameter must be an object, '.
                        'one item per pipeline with name as key and an array with paths to remove as value.'
                    );
                }
                if (!$this->getMultipipeline() && !is_array($removals)) {
                    throw new \LogicException(
                        'In a pipeline, "removeOnAbstinence" param parameter '.
                        'must be an array with paths to remove.'
                    );
                }

                // not empty?
                if (isset($params[$paramName]) && !empty($params[$paramName])) {
                    // skip
                    continue;
                }

                // compose paths..
                if ($this->getMultipipeline()) {
                    foreach ($removals as $pipelineName => $paths) {
                        foreach ($paths as $path) {
                            $pathsToRemove[] = '/'.$pipelineName.$path;
                        }
                    }
                } else {
                    foreach ($removals as $path) {
                        $pathsToRemove[] = $path;
                    }
                }
            }
        }

        if (empty($pathsToRemove)) {
            return $encodedPipeline;
        }

        // remove paths
        $ops = [];
        $sortArr = [];
        foreach ($pathsToRemove as $path) {
            $ops[] = [
                'op' => 'remove',
                'path' => $path
            ];
            $sortArr[] = $path;
        }

        array_multisort($sortArr, SORT_DESC, SORT_NATURAL, $ops);

        try {
            $patcher = new Patch($encodedPipeline, json_encode($ops));
            $encodedPipeline = $patcher->apply();
        } catch (\Exception $exp) {
            throw new \LogicException(
                'Unable to patch the pipeline nodes according to param specification, '.
                'probably wrong parameter definition?',
                $exp
            );
        }

        return $encodedPipeline;
    }
}
