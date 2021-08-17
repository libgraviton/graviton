<?php
/**
 * HttpLoader
 */

namespace Graviton\ProxyBundle\Definition\Loader;

use Graviton\ProxyBundle\Definition\ApiDefinition;
use Graviton\ProxyBundle\Definition\Loader\DispersalStrategy\DispersalStrategyInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client;
use Laminas\Diactoros\Uri;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * load a file over http and process the data
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class HttpLoader implements LoaderInterface
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DispersalStrategyInterface
     */
    private $strategy;

    /**
     * cache
     *
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * cache lifetime
     *
     * @var int
     */
    private $cacheLifetime;

    /**
     * @var array
     */
    private $options = [
        'storeKey' => 'httpLoader',
    ];

    /**
     * constructor
     *
     * @param ValidatorInterface $validator validator
     * @param Client             $client    http client
     * @param LoggerInterface    $logger    Logger
     */
    public function __construct(ValidatorInterface $validator, Client $client, LoggerInterface $logger)
    {
        $this->validator = $validator;
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     *
     * @param DispersalStrategyInterface $strategy dispersal strategy
     *
     * @return void
     */
    public function setDispersalStrategy(DispersalStrategyInterface $strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * @inheritDoc
     *
     * @param CacheItemPoolInterface $cache          cache adapter
     * @param string                 $cacheNamespace cache namespace
     * @param int                    $cacheLifetime  cache lifetime
     *
     * @return void
     */
    public function setCache(CacheItemPoolInterface $cache, $cacheLifetime)
    {
        $this->cache = $cache;
        $this->cacheLifetime = $cacheLifetime;
    }

    /**
     * @inheritDoc
     *
     * @param array $options cache strategy
     *
     * @return void
     */
    public function setOptions($options)
    {
        if (!empty($options['prefix'])) {
            $options['storeKey'] = $options['prefix'];
            unset($options['prefix']);
        }

        $this->options = array_merge($this->options, $options);
    }

    /**
     * check if the url is valid
     *
     * @param string $url url
     *
     * @return boolean
     */
    public function supports($url)
    {
        $error = $this->validator->validate($url, [new Url()]);

        return 0 === count($error);
    }

    /**
     * @inheritDoc
     *
     * @param string $input url
     *
     * @return ApiDefinition
     */
    public function load($input)
    {
        $retVal = new ApiDefinition();
        if (is_null($input)) {
            // if no thirdparty defined; abort now..
            return $retVal;
        }

        $cacheKeyDef = $this->options['storeKey'].'-def';
        if ($this->cache instanceof CacheItemPoolInterface && $this->cache->hasItem($cacheKeyDef)) {
            return $this->cache->getItem($cacheKeyDef)->get();
        }

        if (isset($this->strategy)) {
            $request = new Request('GET', $input);
            $content = $this->fetchFile($request);

            if (empty($content)) {
                return $retVal;
            }

            // store current host (name or ip) serving the API. This MUST be the host only and does not include the
            // scheme nor sub-paths. It MAY include a port. If the host is not included, the host serving the
            // documentation is to be used (including the port)
            $fallbackHost = [];

            // compose base url host
            $uri = new Uri();
            $uri = $uri->withHost($request->getUri()->getHost())
                       ->withScheme($request->getUri()->getScheme())
                       ->withPort($request->getUri()->getPort());

            $fallbackHost['host'] = (string) $uri;

            if ($this->strategy->supports($content)) {
                $retVal = $this->strategy->process($content, $fallbackHost);
            }

            if ($this->cache instanceof CacheItemPoolInterface) {
                $cacheItem = $this->cache->getItem($cacheKeyDef);
                $cacheItem->set($retVal);
                $cacheItem->expiresAfter($this->cacheLifetime);

                $this->cache->save($cacheItem);
            }
        }

        return $retVal;
    }

    /**
     * fetch file from remote destination
     *
     * @param RequestInterface $request request
     *
     * @return string
     */
    private function fetchFile(RequestInterface $request)
    {
        $content = "";
        try {
            $response = $this->client->send($request);
            $content = (string) $response->getBody();
        } catch (RequestException $e) {
            $this->logger->info(
                "Unable to fetch File!",
                [
                    "message" => $e->getMessage(),
                    "url" => $request->getRequestTarget(),
                    "code" => (!empty($e->getResponse())? $e->getResponse()->getStatusCode() : 500)
                ]
            );
        }

        return $content;
    }
}
