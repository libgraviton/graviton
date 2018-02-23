<?php
/**
 * load definitions from a source
 *
 * This Loader implements the following strategies.
 * - file
 * - directory
 * - scan
 * - mongodb
 */

namespace Graviton\GeneratorBundle\Definition\Loader;

use Graviton\GeneratorBundle\Definition\Loader\Strategy\StrategyInterface;
use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Graviton\JsonSchemaBundle\Exception\ValidationException;
use Graviton\JsonSchemaBundle\Validator\InvalidJsonException;
use Graviton\JsonSchemaBundle\Validator\ValidatorInterface;
use JMS\Serializer\SerializerInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class Loader implements LoaderInterface
{
    /**
     * @var StrategyInterface[]
     */
    private $strategies = [];
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * Constructor
     *
     * @param ValidatorInterface  $validator  Validator
     * @param SerializerInterface $serializer Serializer
     */
    public function __construct(ValidatorInterface $validator, SerializerInterface $serializer)
    {
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    /**
     * add a strategy to the loader
     *
     * @param StrategyInterface $strategy strategy to add
     *
     * @return Loader
     */
    public function addStrategy(StrategyInterface $strategy)
    {
        $this->strategies[] = $strategy;
    }

    /**
     * load from input
     *
     * @param string|null $input input from command
     *
     * @return JsonDefinition[]
     */
    public function load($input)
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($input)) {
                return array_map([$this, 'createJsonDefinition'], $strategy->load($input));
            }
        }

        return [];
    }

    /**
     * Deserialize JSON definition
     *
     * @param string $json JSON code
     * @return JsonDefinition
     * @throws InvalidJsonException  If JSON is invalid
     * @throws ValidationException   If definition is not valid
     */
    protected function createJsonDefinition($json)
    {
        $errors = $this->validator->validateJsonDefinition($json);
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        $definition = $this->serializer->deserialize(
            $json,
            'Graviton\\GeneratorBundle\\Definition\\Schema\\Definition',
            'json'
        );

        return new JsonDefinition($definition);
    }
}
