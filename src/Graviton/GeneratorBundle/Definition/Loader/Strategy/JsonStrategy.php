<?php
namespace Graviton\GeneratorBundle\Definition\Loader\Strategy;

/**
 * Load definition from JSON string
 */
class JsonStrategy extends AbstractStrategy
{
    /**
     * @inheritdoc
     */
    public function supports($input)
    {
        return is_string($input) && $input[0] === '{';
    }

    /**
     * @inheritdoc
     */
    public function getJsonDefinitions($input)
    {
        return [$input];
    }
}
