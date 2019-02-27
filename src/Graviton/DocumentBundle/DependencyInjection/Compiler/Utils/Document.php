<?php
/**
 * Document class file
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler\Utils;

/**
 * Document
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class Document
{
    /**
     * @var string
     */
    private $className;
    /**
     * @var AbstractField[]
     */
    private $fields = [];
    /**
     * @var array
     */
    private $solrFields = [];
    /**
     * @var array
     */
    private $solrAggregate = [];

    /**
     * Constructor
     *
     * @param string $className Class name
     * @param array  $fields    Fields
     */
    public function __construct($className, array $fields)
    {
        $this->className = $className;
        $this->fields = $fields;
    }

    /**
     * Get class name
     *
     * @return string
     */
    public function getClass()
    {
        return $this->className;
    }

    /**
     * Get fields
     *
     * @return AbstractField[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * get SolrFields
     *
     * @return array SolrFields
     */
    public function getSolrFields()
    {
        return $this->solrFields;
    }

    /**
     * set SolrFields
     *
     * @param array $solrFields solrFields
     *
     * @return void
     */
    public function setSolrFields($solrFields)
    {
        $this->solrFields = $solrFields;
    }

    /**
     * get solr aggregate
     *
     * @return array solr aggregate
     */
    public function getSolrAggregate()
    {
        return $this->solrAggregate;
    }

    /**
     * set solr aggregate
     *
     * @param array $solrAggregate solr aggregate
     *
     * @return void
     */
    public function setSolrAggregate(array $solrAggregate)
    {
        $this->solrAggregate = $solrAggregate;
    }
}
