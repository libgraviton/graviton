<?php
/**
 * ApiDefinitionLoader
 */

namespace Graviton\ProxyBundle\Service;

use Graviton\ProxyBundle\Definition\ApiDefinition;
use Graviton\ProxyBundle\Definition\Loader\LoaderInterface;

/**
 * load API definition from  a external source
 *
 * @package Graviton\ProxyBundle\Service
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link    http://swisscom.ch
 */
class ApiDefinitionLoader
{
    /**
     * @var string
     */
    const PROXY_ROUTE = "3rdparty";

    /**
     * @var LoaderInterface
     */
    private $definitionLoader;

    /**
     * @var array
     */
    private $options;

    /**
     * @var ApiDefinition
     */
    private $definition;

    /**
     * set loader
     *
     * @param LoaderInterface $loader loader
     *
     * @return void
     */
    public function setDefinitionLoader($loader)
    {
        $this->definitionLoader = $loader;
    }

    /**
     * set options for the loader
     *
     * @param array $options options [uri, prefix]
     *
     * @return void
     */
    public function setOption(array $options)
    {
        $this->options = $options;
    }

    /**
     * get a schema for one endpoint
     *
     * @param string $endpoint
     *
     * @return \stdClass
     */
    public function getEndpointSchema($endpoint)
    {
        $this->loadApiDefinition();

        return $this->definition->getSchema($endpoint);
    }

    /**
     * get an endpoint
     *
     * @param string  $endpoint $endpoint
     * @param boolean $withHost attach host name to the url
     *
     * @return string
     */
    public function getEndpoint($endpoint, $withHost = false)
    {
        $this->loadApiDefinition();
        $url = "";
        if ($withHost) {
            $url = $this->definition->getHost();
        }

        $endpoints = $this->definition->getEndpoints(false);
        if (in_array($endpoint, $endpoints)) {
            $url .= $endpoint;
        }

        return $url;
    }

    /**
     * get all endpoints for an API
     *
     * @param boolean $withHost attach host name to the url
     *
     * @return array
     */
    public function getAllEndpoints($withHost = false)
    {
        $this->loadApiDefinition();

        $prefix = self::PROXY_ROUTE;
        if (isset($this->options['prefix'])) {
            $prefix .= "/".$this->options['prefix'];
        }

        $endpoints = $this->definition->getEndpoints($withHost, $prefix);

        return $endpoints;
    }

    /**
     * internal load method
     */
    private function loadApiDefinition()
    {
        $supports = $this->definitionLoader->supports($this->options['uri']);
        if ($this->definition == null && $supports) {
            $this->definition = $this->definitionLoader->load($this->options['uri']);
        } elseif (!$supports) {
            throw new \RuntimeException("This resource (".$this->options['uri'].") is not supported.");
        }
    }
}
