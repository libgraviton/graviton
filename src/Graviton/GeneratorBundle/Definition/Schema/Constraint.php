<?php
namespace Graviton\GeneratorBundle\Definition\Schema;

/**
 * JSON definition "target.fields.constraints"
 */
class Constraint
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var ConstraintOption[]
     */
    private $options = [];

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return ConstraintOption[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param ConstraintOption[] $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }
}
