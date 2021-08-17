<?php
/**
 * cache factory
 */

namespace Graviton\CacheBundle\Factory;

use Psr\Cache\CacheItemPoolInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class CacheFactory
{

    private $appCache;
    private $redisHost;
    private $redisPort;

    public function __construct(CacheItemPoolInterface $appCache, $redisHost, $redisPort)
    {
        $this->appCache = $appCache;
        $this->redisHost = $redisHost;
        $this->redisPort = $redisPort;
    }

    public function getInstance(bool $isAppendable) : CacheItemPoolInterface {
        return $this->appCache;
    }
}
