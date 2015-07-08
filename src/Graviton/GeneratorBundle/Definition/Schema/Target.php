<?php
namespace Graviton\GeneratorBundle\Definition\Schema;

/**
 * JSON definition "target"
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
     * @param Index[] $indexes
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
     * @param Relation[] $relations
     * @return $this
     */
    public function setRelations(array $relations)
    {
        $this->relations = $relations;
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
     * @param Field[] $fields
     * @return $this
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @param Field $field
     * @return $this
     */
    public function addField(Field $field)
    {
        $this->fields[] = $field;
        return $this;
    }
}
