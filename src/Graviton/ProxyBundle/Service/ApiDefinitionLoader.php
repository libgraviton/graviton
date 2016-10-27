<?php
/**
 * ApiDefinitionLoader
 */

namespace Graviton\ProxyBundle\Service;

use Graviton\ProxyBundle\Definition\ApiDefinition;
use Graviton\ProxyBundle\Definition\Loader\LoaderFactory;
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

    /** @var  LoaderFactory */
    private $loaderFactory;


    /**
     * ApiDefinitionLoader constructor.
     *
     * @param LoaderFactory $loaderFactory Factory to initiate an apiloader
     */
    public function __construct(LoaderFactory $loaderFactory)
    {
        $this->loaderFactory = $loaderFactory;
    }

    /**
     * set loader
     *
     * @param LoaderInterface $loader loader
     *
     * @return void
     * @throws \RuntimeException
     */
    public function setDefinitionLoader($loader)
    {
        throw new \RuntimeException('ApiDefinitionLoader::setDefinitionLoader is deprecated.');
    }

    /**
     * Provides the definition loader instance.
     *
     * @return LoaderInterface
     */
    public function getDefinitionLoader()
    {
        if (empty($this->definitionLoader) && array_key_exists('prefix', $this->options)) {
            $this->definitionLoader = $this->loaderFactory->create($this->options['prefix']);
        }

        return $this->definitionLoader;
    }

    /**
     * Resets the definition loader
     *
     * @return void
     */
    public function resetDefinitionLoader()
    {
        $this->definitionLoader = null;
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
        $this->getDefinitionLoader()->setOptions($options);
    }

    /**
     * @param array $options Options to be added
     *
     * @return void
     */
    public function addOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);

        $this->getDefinitionLoader()->setOptions($options);
    }

    /**
     * get the origin service definition
     *
     * @param bool $forceReload Switch to force a new api definition object will be provided.
     *
     * @return mixed the origin service definition (type depends on dispersal strategy)
     */
    public function getOriginDefinition($forceReload = false)
    {
        $this->loadApiDefinition($forceReload);

        return $this->definition->getOrigin();
    }

    /**
     * get a schema for one endpoint
     *
     * @param string $endpoint    endpoint
     * @param bool   $forceReload Switch to force a new api definition object will be provided.
     *
     * @return \stdClass
     */
    public function getEndpointSchema($endpoint, $forceReload = false)
    {
        $this->loadApiDefinition($forceReload);

        return $this->definition->getSchema($endpoint);
    }

    /**
     * get an endpoint
     *
     * @param string  $endpoint    endpoint
     * @param boolean $withHost    attach host name to the url
     * @param bool    $forceReload Switch to force a new api definition object will be provided.
     *
     * @return string
     */
    public function getEndpoint($endpoint, $withHost = false, $forceReload = false)
    {
        $this->loadApiDefinition($forceReload);
        $url = "";
        if ($withHost) {
            $url = empty($this->options['host']) ? $this->definition->getHost() : $this->options['host'];
        }

        // If the base path is not already included, we need to add it.
        $url .= (empty($this->options['includeBasePath']) ? $this->definition->getBasePath() : '') . $endpoint;

        return $url;
    }

    /**
     * get all endpoints for an API
     *
     * @param boolean $withHost    attach host name to the url
     * @param bool    $forceReload Switch to force a new api definition object will be provided.
     *
     * @return array
     */
    public function getAllEndpoints($withHost = false, $forceReload = false)
    {
        $this->loadApiDefinition($forceReload);

        $host = empty($this->options['host']) ? '' : $this->options['host'];
        $prefix = self::PROXY_ROUTE;
        if (isset($this->options['prefix'])) {
            $prefix .= "/".$this->options['prefix'];
        }

        return !is_object($this->definition) ? [] : $this->definition->getEndpoints(
            $withHost,
            $prefix,
            $host,
            !empty($this->options['includeBasePath'])
        );
    }

    /**
     * internal load method
     *
     * @param bool $forceReload Switch to force a new api definition object will be provided.
     *
     * @return void
     */
    private function loadApiDefinition($forceReload = false)
    {
        $definitionLoader = $this->getDefinitionLoader();

        $supported = $definitionLoader->supports($this->options['uri']);

        if ($forceReload || ($this->definition == null && $supported)) {
            $this->definition = $definitionLoader->load($this->options['uri']);
        } elseif (!$supported) {
            throw new \RuntimeException("This resource (".$this->options['uri'].") is not supported.");
        }
    }
}
