<?php
namespace Graviton\EmbedTestBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDb;


/**
 * @MongoDb\Document(collection="test_embed_document")
 */
class Document
{
    /**
     * @var string
     * @MongoDb\Id(strategy="UUID")
     */
    private $id;
    /**
     * @var string
     * @MongoDb\Field(type="string")
     */
    private $name;
    /**
     * @var Embedded
     * @MongoDb\EmbedOne(targetDocument="Embedded")
     */
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
