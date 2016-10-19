<?php

namespace Graviton\ProxyBundle\Definition\Loader;

use Graviton\ProxyBundle\Exception\LoaderException;


/**
 * Class LoaderFactory
 *
 * @package Graviton\ProxyBundle\Definition\Loader
 */
class LoaderFactory
{
    /** @var array  */
    private $loader;


    /**
     * LoaderFactory constructor.
     *
     * @param array $loader
     */
    public function __construct(array $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Provides a list of registered loaders
     *
     * @return array
     */
    public function getLoaderDefinitions()
    {
        return $this->loader;
    }

    /**
     * Provides
     *
     * @param string $source
     *
     * @return LoaderInterface
     *
     * @throws LoaderException
     */
    public function create($source)
    {
        if (array_key_exists($source, $this->loader)) {

            return $this->loader[$source];
        }

        throw new LoaderException('Expected Loader for source ('. $source .') does not exist.');
    }
}
