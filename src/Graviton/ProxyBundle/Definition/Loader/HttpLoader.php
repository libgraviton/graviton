<?php
/**
 * HttpLoader
 */

namespace Graviton\ProxyBundle\Definition\Loader;

use Graviton\ProxyBundle\Definition\ApiDefinition;
use Graviton\ProxyBundle\Definition\Loader\DispersalStrategy\DispersalStrategyInterface;
use Doctrine\Common\Cache\CacheProvider;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zend\Diactoros\Uri;

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
     * doctrine cache
     *
     * @var CacheProvider
     */
    private $cache;

    /**
     * doctrine cache lifetime
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
     * @param CacheProvider $cache          doctrine cache provider
     * @param string        $cacheNamespace cache namespace
     * @param int           $cacheLifetime  cache lifetime
     *
     * @return void
     */
    public function setCache(CacheProvider $cache, $cacheNamespace, $cacheLifetime)
    {
        $this->cache = $cache;
        $this->cache->setNamespace($cacheNamespace);
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
        if ($this->cache instanceof CacheProvider && $this->cache->contains($cacheKeyDef)) {
            return $this->cache->fetch($cacheKeyDef);
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

            if ($this->cache instanceof CacheProvider) {
                $this->cache->save($cacheKeyDef, $retVal, $this->cacheLifetime);
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
