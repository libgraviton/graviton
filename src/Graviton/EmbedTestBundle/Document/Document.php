<?php
namespace Graviton\EmbedTestBundle\Document;

class Document
{
    private $id;
    private $name;
    private $embedded;

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
}
