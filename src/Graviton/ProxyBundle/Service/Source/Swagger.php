<?php
/**
 * Swagger
 */

namespace Graviton\ProxyBundle\Service\Source;

use Doctrine\Common\Cache\CacheProvider;
use Graviton\ProxyBundle\Adapter\Guzzle\GuzzleAdapter;
use Graviton\ProxyBundle\Definition\ApiDefinition;
use Graviton\ProxyBundle\Definition\Loader\DispersalStrategy\DispersalStrategyInterface;

/**
 * Class Swagger
 *
 * @package Graviton\ProxyBundle\Service\Source
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link    http://swisscom.ch
 */
class Swagger implements SourceInterface
{
    /** @var string  */
    private $prefix;

    /** @var string  */
    private $uri;

    /** @var GuzzleAdapter  */
    private $client;

    /** @var DispersalStrategyInterface */
    private $strategy;

    /** @var CacheProvider */
    private $cache;

    /** @var string  */
    private $cacheId;

    /** @var array */
    private $options;

    /** cache item lifetime */
    const CACHE_TTL = 86400;

    /** cache namespace */
    const CACHE_NS = 'Graviton/ProxyBundle';


    /**
     * Swagger constructor.
     *
     * @param GuzzleAdapter              $client
     * @param DispersalStrategyInterface $strategy
     * @param CacheProvider              $cache
     * @param string                     $prefix Identifier of the source used in the path (serivce://3rdparty/{$prefix}/[...])
     * @param string                     $uri    Url of the swagger file to be retrieved
     * @param array                      $options
     */
    public function __construct(
        GuzzleAdapter $client,
        DispersalStrategyInterface $strategy,
        CacheProvider $cache,
        $prefix,
        $uri,
        array $options
    )
    {
        $this->client = $client;
        $this->prefix = $prefix;
        $this->uri = $uri;
        $this->strategy = $strategy;
        $this->initCache($cache, sha1($uri));
        $this->options = $options;
    }

    /**
     *
     * @param string $endpoint
     * @param bool   $withHost
     *
     * @return string
     */
    public function buildUrl($endpoint, $withHost = false)
    {
        $url = "";
        $apiDefinition = $this->receiveSwaggerData();

        if ($withHost) {
            $url = empty($this->options['host']) ? $apiDefinition->getHost() : $this->options['host'];
        }

        // If the base path is not already included, we need to add it.
        $url .= (empty($this->options['includeBasePath']) ? $apiDefinition->getBasePath() : '') . $endpoint;

        return $url;
    }

    /**
     * @param CacheProvider $cache Used cache provider
     * @param string        $id    Identifier for the cache item
     */
    private function initCache(CacheProvider $cache, $id)
    {
        $this->cache = $cache;
        $this->cacheId = $id;
        $this->cache->setNamespace(self::CACHE_NS);
    }

    /**
     * @return ApiDefinition
     */
    private function receiveSwaggerData()
    {
        // 1. is file in cache? › if so return
        if ($this->cache->contains($this->cacheId)) {
            $apiDef = $this->cache->fetch($this->cacheId);
            if (false == $apiDef) {
                $this->cache->delete($this->cacheId);
                return $this->receiveSwaggerData();
            }
        }

        $apiDef = new ApiDefinition();
        // get swagger.json from Source
        // TODO [taafeba2]: exception handling
        $content = $this->client->request('GET', $this->uri);

        // store current host (name or ip) serving the API. This MUST be the host only and does not include the
        // scheme nor sub-paths. It MAY include a port. If the host is not included, the host serving the
        // documentation is to be used (including the port)
        $parts = parse_url($this->uri);

        $fallbackHost = array();
        $fallbackHost['host'] = sprintf(
            '%s://%s:%d',
            $parts['scheme'],
            $parts['host'],
            $parts['port']
        );

        if ($this->strategy->supports($content)) {
            $apiDef = $this->strategy->process($content, $fallbackHost);
            $this->cache->save($this->cacheId, $apiDef, self::CACHE_TTL);
        }

        return $apiDef;
    }
}
