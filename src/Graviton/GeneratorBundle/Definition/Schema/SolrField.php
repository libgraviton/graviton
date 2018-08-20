<?php
/**
 * Part of JSON definition
 */
namespace Graviton\GeneratorBundle\Definition\Schema;

/**
 * JSON definition "solr.fields"
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SolrField
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $type;
    /**
     * @var double
     */
    private $weight;

    /**
     * get Name
     *
     * @return string Name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * set Name
     *
     * @param string $name name
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * get Type
     *
     * @return string Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * set Type
     *
     * @param string $type type
     *
     * @return void
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * get Weight
     *
     * @return float Weight
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * set Weight
     *
     * @param float $weight weight
     *
     * @return void
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }
}
