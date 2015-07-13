<?php
namespace Graviton\GeneratorBundle\Definition\Loader\Strategy;

/**
 * Load definition from JSON string
 */
class JsonStrategy extends AbstractStrategy
{
    /**
     * may the strategy handle this input
     *
     * @param string|null $input input from command
     *
     * @return boolean
     */
    public function supports($input)
    {
        return is_string($input) && strlen($input) > 0 && $input[0] === '{';
    }

    /**
     * @param mixed $input Input from command
     * @return string[]
     */
    public function getJsonDefinitions($input)
    {
        return [$input];
    }
}
