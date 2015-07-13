<?php
/**
 * AbstractStrategy class file
 */
namespace Graviton\GeneratorBundle\Definition\Loader\Strategy;

use Graviton\GeneratorBundle\Definition\Schema\Definition;
use Graviton\GeneratorBundle\Definition\JsonDefinition;
use JMS\Serializer\SerializerInterface;

/**
 * Abstract loader strategy
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
abstract class AbstractStrategy implements StrategyInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param mixed $input Input from command
     * @return string[]
     */
    abstract protected function getJsonDefinitions($input);

    /**
     * @param SerializerInterface $serializer Serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param string $json JSON code
     * @return Definition
     */
    protected function deserializeDefinition($json)
    {
        return $this->serializer->deserialize($json, 'Graviton\GeneratorBundle\Definition\Schema\Definition', 'json');
    }

    /**
     * load
     *
     * @param string|null $input input from command
     *
     * @return JsonDefinition[]
     */
    public function load($input)
    {
        return array_map(
            function ($json) {
                return new JsonDefinition($this->deserializeDefinition($json));
            },
            $this->getJsonDefinitions($input)
        );
    }
}
