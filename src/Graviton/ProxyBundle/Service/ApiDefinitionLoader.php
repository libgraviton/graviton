<?php
/**
 * Created by PhpStorm.
 * User: samuel
 * Date: 09.09.15
 * Time: 10:25
 */

namespace Graviton\ProxyBundle\Service;


use Graviton\ProxyBundle\Definition\Loader\LoaderInterface;

class ApiDefinitionLoader
{
    /**
     * @var LoaderInterface
     */
    private $definitionLoader;

    /**
     * @var array
     */
    private $options;

    public function __construct() {

    }

    public function setDefinitionLoader($loader) {
        $this->definitionLoader = $loader;
    }

    public function setOption(array $options) {
        $this->options = $options;
    }

    public function getEndpointScheme($apiName, $endpoint) {

    }

    public function getAllEndpoints($apiname) {

        if ($this->definitionLoader->supports($this->options['uri'])) {
            $definition = $this->definitionLoader->load($this->options['uri']);
        }

        return $definition;
    }
}