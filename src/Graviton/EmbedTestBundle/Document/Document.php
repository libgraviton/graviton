<?php
namespace Graviton\EmbedTestBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;

class Document
{
    private $id;
    private $name;
    private $embedded;
    private $embeddeds;

    public function __construct()
    {
        $this->embeddeds = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getEmbedded()
    {
        return $this->embedded;
    }

    public function setEmbedded(Embedded $embedded)
    {
        $this->embedded = $embedded;
        return $this;
    }

    public function getEmbeddeds()
    {
        return $this->embeddeds;
    }

    public function setEmbeddeds($embeddeds)
    {
        $this->embeddeds = $embeddeds;
        return $this;
    }

    public function addEmbedded(Embedded $embedded)
    {
        $this->embeddeds[] = $embedded;
        return $this;
    }
}
