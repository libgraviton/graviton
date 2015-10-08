<?php
namespace Graviton\EmbedTestBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDb;

/**
 * @MongoDb\EmbeddedDocument
 */
class Embedded
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
}
