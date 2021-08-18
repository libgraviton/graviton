<?php
/**
 * LoaderInterface
 */

namespace Graviton\ProxyBundle\Definition\Loader;

use Graviton\ProxyBundle\Definition\ApiDefinition;
use Graviton\ProxyBundle\Definition\Loader\DispersalStrategy\DispersalStrategyInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * LoaderInterface
 *
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license https://opensource.org/licenses/MIT MIT License
 * @link    http://swisscom.ch
 */
interface LoaderInterface
{
    /**
     * set options
     *
     * @param array $options array of options
     *
     * @return void
     */
    public function setOptions($options);

    /**
     * set a load strategy
     *
     * @param DispersalStrategyInterface $strategy strategy to add
     *
     * @return void
     */
    public function setDispersalStrategy(DispersalStrategyInterface $strategy);

    /**
     * set a cache
     *
     * @param CacheItemPoolInterface $cache         cache
     * @param int                    $cacheLifetime cache lifetime
     *
     * @return void
     */
    public function setCache(CacheItemPoolInterface $cache, $cacheLifetime);

    /**
     * check if the input is supported
     *
     * @param string $input input
     *
     * @return boolean
     */
    public function supports($input);

    /**
     * @param string $input input
     *
     * @return ApiDefinition
     */
    public function load($input);
}
