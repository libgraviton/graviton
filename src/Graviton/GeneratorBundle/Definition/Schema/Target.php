<?php
/**
 * Part of JSON definition
 */
namespace Graviton\GeneratorBundle\Definition\Schema;

/**
 * JSON definition "target"
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class Target
{
    /**
     * @var Index[]
     */
    private $indexes = [];
    /**
     * @var Relation[]
     */
    private $relations = [];
    /**
     * @var Field[]
     */
    private $fields = [];

    /**
     * @return Index[]
     */
    public function getIndexes()
    {
        return $this->indexes;
    }

    /**
     * @param Index[] $indexes Indexes
     * @return $this
     */
    public function setIndexes(array $indexes)
    {
        $this->indexes = $indexes;
        return $this;
    }

    /**
     * @return Relation[]
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * @param Relation[] $relations Relations
     * @return $this
     */
    public function setRelations(array $relations)
    {
        $this->relations = $relations;
        return $this;
    }

    /**
     * @param Relation $relation Relation
     * @return $this
     */
    public function addRelation(Relation $relation)
    {
        $this->relations[] = $relation;
        return $this;
    }

    /**
     * @return Field[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param Field[] $fields Fields
     * @return $this
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @param Field $field Field
     * @return $this
     */
    public function addField(Field $field)
    {
        $this->fields[] = $field;
        return $this;
    }
}
