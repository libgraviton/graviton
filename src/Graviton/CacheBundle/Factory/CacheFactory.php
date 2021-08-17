<?php
/**
 * cache factory
 */

namespace Graviton\CacheBundle\Factory;

use Doctrine\Common\Cache\CacheProvider;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\DoctrineProvider;

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

    public function getInstance(bool $isRewrite = false) : CacheItemPoolInterface {
        return $this->appCache;
    }

    /**
     * returns a doctrine cache instance. this is now only still necessary for mongodb-odm! internally,
     * we should use symfony cache
     *
     * @param bool $isRewrite if true, we assume the user wants to rewrite stuff in the cache, avoiding phpfiles
     *
     * @return CacheProvider
     */
    public function getDoctrineInstance(bool $isRewrite = false) : CacheProvider {
        return new DoctrineProvider($this->getInstance($isRewrite));
    }
}
