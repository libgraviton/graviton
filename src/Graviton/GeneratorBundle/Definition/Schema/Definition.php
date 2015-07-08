<?php
namespace Graviton\GeneratorBundle\Definition\Schema;

/**
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
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
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
     * @param string $description
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
     * @param bool $isSubDocument
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
     * @param Service $service
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
     * @param Target $target
     * @return $this
     */
    public function setTarget(Target $target)
    {
        $this->target = $target;
        return $this;
    }
}
