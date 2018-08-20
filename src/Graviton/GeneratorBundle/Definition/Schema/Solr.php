<?php
/**
 * Part of JSON definition
 */
namespace Graviton\GeneratorBundle\Definition\Schema;

/**
 * JSON definition "solr"
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class Solr
{
    /**
     * @var \stdClass[]
     */
    private $aggregate;
    /**
     * @var SolrField[]
     */
    private $fields;

    /**
     * get Aggregate
     *
     * @return \stdClass[] Aggregate
     */
    public function getAggregate()
    {
        return $this->aggregate;
    }

    /**
     * set Aggregate
     *
     * @param \stdClass[] $aggregate aggregate
     *
     * @return void
     */
    public function setAggregate($aggregate)
    {
        $this->aggregate = $aggregate;
    }

    /**
     * get Fields
     *
     * @return SolrField[] Fields
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * set Fields
     *
     * @param SolrField[] $fields fields
     *
     * @return void
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }
}
