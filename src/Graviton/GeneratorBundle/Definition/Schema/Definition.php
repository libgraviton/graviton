<?php
/**
 * Part of JSON definition
 */
namespace Graviton\GeneratorBundle\Definition\Schema;

/**
 * Raw desrialized data from JSON definition
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class Definition
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    private $description;
    /**
     * @var bool
     */
    private $isSubDocument = false;
    /**
     * @var Service
     */
    private $service;
    /**
     * @var Target
     */
    private $target;
    /**
     * @var string
     */
    private $title;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id ID
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title Title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param string $description Description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsSubDocument()
    {
        return $this->isSubDocument;
    }

    /**
     * @param bool $isSubDocument Is sub document definition
     * @return $this
     */
    public function setIsSubDocument($isSubDocument)
    {
        $this->isSubDocument = $isSubDocument;
        return $this;
    }

    /**
     * @return Service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param Service $service Service data
     * @return $this
     */
    public function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }

    /**
     * @return Target
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param Target $target Target data
     * @return $this
     */
    public function setTarget(Target $target)
    {
        $this->target = $target;
        return $this;
    }
}
