<?php
namespace Graviton\GeneratorBundle\Definition\Loader\Strategy;

use Graviton\GeneratorBundle\Definition\Schema\Definition;
use Graviton\GeneratorBundle\Definition\JsonDefinition;
use JMS\Serializer\SerializerInterface;

/**
 */
abstract class AbstractStrategy implements StrategyInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param mixed $input
     * @return string[]
     */
    abstract protected function getJsonDefinitions($input);

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param string $json
     * @return Definition
     */
    protected function deserializeDefinition($json)
    {
        return $this->serializer->deserialize($json, 'Graviton\GeneratorBundle\Definition\Schema\Definition', 'json');
    }

    /**
     * @inheritdoc
     */
    public function load($input)
    {
        return array_map(function ($json) {
            return new JsonDefinition($this->deserializeDefinition($json));
        }, $this->getJsonDefinitions($input));
    }
}
