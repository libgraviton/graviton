<?php

declare(strict_types=1);

namespace Graviton\DocumentBundle\Serializer\Visitor;

use JMS\Serializer\Visitor\Factory\SerializationVisitorFactory;
use JMS\Serializer\Visitor\SerializationVisitorInterface;

class JsonSerializationVisitorFactory implements SerializationVisitorFactory
{
    /**
     * @var int
     */
    private $options = JSON_PRESERVE_ZERO_FRACTION;

    public function getVisitor(): SerializationVisitorInterface
    {
        return new JsonSerializationVisitor($this->options);
    }

    public function setOptions(int $options): self
    {
        $this->options = $options;
        return $this;
    }
}
