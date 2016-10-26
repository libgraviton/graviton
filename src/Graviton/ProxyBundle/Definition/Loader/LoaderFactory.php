<?php
/**
 * LoaderFactory
 */

namespace Graviton\ProxyBundle\Definition\Loader;

use Graviton\ProxyBundle\Exception\LoaderException;

/**
 * Class LoaderFactory
 *
 * @package Graviton\ProxyBundle\Definition\Loader
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class LoaderFactory
{
    /** @var array  */
    private $loader;


    /**
     * Adds a loader to the set of registered loaders.
     *
     * @param LoaderInterface $loader Loader definintion
     * @param string          $key    Indentifier to find the registered loader
     */
    public function addLoaderDefinition(LoaderInterface $loader, $key)
    {
        $this->loader[$key] = $loader;
    }

    /**
     * Provides the definition loader identified by the given key.
     *
     * @param string $key Name of the loader to be returned
     *
     * @return LoaderInterface|null
     */
    public function getLoaderDefinition($key)
    {
        if (array_key_exists($key, $this->loader)) {
            return $this->loader[$key];
        }

        return null;
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
     * @param string $source Information of what loader to be initiated.
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
