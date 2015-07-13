<?php
/**
 * Part of JSON definition
 */
namespace Graviton\GeneratorBundle\Definition\Schema;

/**
 * JSON definition "target.fields.relations"
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class Relation
{
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $collectionName;
    /**
     * @var string
     */
    private $localProperty;
    /**
     * @var string
     */
    private $localValueField;
    /**
     * @var string
     */
    private $foreignProperty;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type Relation type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getCollectionName()
    {
        return $this->collectionName;
    }

    /**
     * @param string $collectionName Relation collection name
     * @return $this
     */
    public function setCollectionName($collectionName)
    {
        $this->collectionName = $collectionName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocalProperty()
    {
        return $this->localProperty;
    }

    /**
     * @param string $localProperty Local relation field
     * @return $this
     */
    public function setLocalProperty($localProperty)
    {
        $this->localProperty = $localProperty;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocalValueField()
    {
        return $this->localValueField;
    }

    /**
     * @param string $localValueField Local property name
     * @return $this
     */
    public function setLocalValueField($localValueField)
    {
        $this->localValueField = $localValueField;
        return $this;
    }

    /**
     * @return string
     */
    public function getForeignProperty()
    {
        return $this->foreignProperty;
    }

    /**
     * @param string $foreignProperty Foreign relation field
     * @return $this
     */
    public function setForeignProperty($foreignProperty)
    {
        $this->foreignProperty = $foreignProperty;
        return $this;
    }
}
