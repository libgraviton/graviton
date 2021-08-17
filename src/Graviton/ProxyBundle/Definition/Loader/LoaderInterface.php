<?php
/**
 * LoaderInterface
 */

namespace Graviton\ProxyBundle\Definition\Loader;

use Graviton\ProxyBundle\Definition\ApiDefinition;
use Graviton\ProxyBundle\Definition\Loader\DispersalStrategy\DispersalStrategyInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;

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
     * @param AdapterInterface $cache         cache
     * @param int              $cacheLifetime cache lifetime
     *
     * @return void
     */
    public function setCache(AdapterInterface $cache, $cacheLifetime);

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
