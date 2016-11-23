<?php
/**
 * HttpLoader
 */

namespace Graviton\ProxyBundle\Definition\Loader;

use Graviton\ProxyBundle\Definition\ApiDefinition;
use Graviton\ProxyBundle\Definition\Loader\DispersalStrategy\DispersalStrategyInterface;
use Doctrine\Common\Cache\CacheProvider;
use Guzzle\Http\Client;
use Guzzle\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * load a file over http and process the data
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
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
     * @var array curl options to apply on each request
     */
    private $curlOptions = [];

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
     * set curl options
     *
     * @param array $curlOptions the curl options
     *
     * @return void
     */
    public function setCurlOptions(array $curlOptions)
    {
        $this->curlOptions = $curlOptions;
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
     * Applies the specified curl option on a request
     *
     * @param RequestInterface $request request
     *
     * @return void
     */
    protected function applyCurlOptions($request)
    {
        $curl = $request->getCurlOptions();
        foreach ($this->curlOptions as $option => $value) {
            $option = 'CURLOPT_'.strtoupper($option);
            $curl->set(constant($option), $value);
        }
        $curl->set(constant('CURLOPT_CAINFO'), __DIR__.'/../../Resources/cert/cacert.pem');
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
        if (isset($this->strategy)) {
            $request = $this->client->get($input);
            $this->applyCurlOptions($request);
            if (isset($this->cache) && $this->cache->contains($this->options['storeKey'])) {
                $content = $this->cache->fetch($this->options['storeKey']);

                if (empty($content)) {
                    $content = $this->fetchFile($request);
                }
            } else {
                //$content = $this->fetchFile($request);
                $content = file_get_contents('/Users/taafeba2/development/swisscom/graviton-api-zgkb-bap/vendor/graviton/graviton/app/cache/swagger.json');
            }

            // store current host (name or ip) serving the API. This MUST be the host only and does not include the
            // scheme nor sub-paths. It MAY include a port. If the host is not included, the host serving the
            // documentation is to be used (including the port)
            $fallbackHost = array();
            $fallbackHost['host'] = sprintf(
                '%s://%s:%d',
                $request->getScheme(),
                $request->getHost(),
                $request->getPort()
            );
            if ($this->strategy->supports($content)) {
                $retVal = $this->strategy->process($content, $fallbackHost);
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
        $content = "{}";
        try {
            $response = $request->send();
            $content = $response->getBody(true);
            if (isset($this->cache)) {
                $this->cache->save($this->options['storeKey'], $content, $this->cacheLifetime);
            }
        } catch (\Guzzle\Http\Exception\HttpException $e) {
            $this->logger->info(
                "Unable to fetch File!",
                [
                    "message" => $e->getMessage(),
                    "url" => $request->getUrl(),
                    "code" => (!empty($request->getResponse())? $request->getResponse()->getStatusCode() : 500)
                ]
            );
        }

        return $content;
    }
}
