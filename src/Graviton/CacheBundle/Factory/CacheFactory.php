<?php
/**
 * cache factory
 */

namespace Graviton\CacheBundle\Factory;

use Symfony\Component\Cache\Adapter\AdapterInterface;

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

    public function __construct(AdapterInterface $appCache, $redisHost, $redisPort)
    {
        $this->appCache = $appCache;
        $this->redisHost = $redisHost;
        $this->redisPort = $redisPort;
    }

    public function getInstance(bool $isAppendable) : AdapterInterface {
        return $this->appCache;
    }
}
