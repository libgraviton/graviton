<?php
/**
 * Part of JSON definition
 */
namespace Graviton\GeneratorBundle\Definition\Schema;

/**
 * JSON definition "target.fields"
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class Field
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
     * @var int
     */
    private $length;
    /**
     * @var string
     */
    private $title;
    /**
     * @var string
     */
    private $description;
    /**
     * @var string
     */
    private $exposeAs;
    /**
     * @var bool
     */
    private $readOnly = false;
    /**
     * @var bool
     */
    private $required = false;
    /**
     * @var bool
     */
    private $translatable = false;
    /**
     * @var Constraint[]
     */
    private $constraints = [];
    /**
     * @var array
     */
    private $collection;

    /**
     * @var array
     */
    private $collection = [];

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name Field name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type Field type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param int $length Field length
     * @return $this
     */
    public function setLength($length)
    {
        $this->length = $length;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title Field title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description Field description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getExposeAs()
    {
        return $this->exposeAs;
    }

    /**
     * @param string $exposeAs Expose field as ...
     * @return $this
     */
    public function setExposeAs($exposeAs)
    {
        $this->exposeAs = $exposeAs;
        return $this;
    }

    /**
     * @return bool
     */
    public function getReadOnly()
    {
        return $this->readOnly;
    }

    /**
     * @param bool $readOnly Is field readonly
     * @return $this
     */
    public function setReadOnly($readOnly)
    {
        $this->readOnly = $readOnly;
        return $this;
    }

    /**
     * @return bool
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * @param bool $required Is field required
     * @return $this
     */
    public function setRequired($required)
    {
        $this->required = $required;
        return $this;
    }

    /**
     * @return bool
     */
    public function getTranslatable()
    {
        return $this->translatable;
    }

    /**
     * @param bool $translatable Is field translatable
     * @return $this
     */
    public function setTranslatable($translatable)
    {
        $this->translatable = $translatable;
        return $this;
    }

    /**
     * @return Constraint[]
     */
    public function getConstraints()
    {
        return $this->constraints;
    }

    /**
     * @param Constraint[] $constraints Field constraints
     * @return $this
     */
    public function setConstraints(array $constraints)
    {
        $this->constraints = $constraints;
        return $this;
    }

    /**
     * @return array
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @param array $collection Field
     * @return $this
     */
    public function setCollection(array $collection)
    {
        $this->collection = $collection;
        return $this;
    }
}
