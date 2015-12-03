<?php
/**
 * HttpLoader
 */

namespace Graviton\ProxyBundle\Definition\Loader;

use Graviton\ProxyBundle\Definition\ApiDefinition;
use Graviton\ProxyBundle\Definition\Loader\CacheStrategy\CacheStrategyInterface;
use Graviton\ProxyBundle\Definition\Loader\DispersalStrategy\DispersalStrategyInterface;
use Guzzle\Http\Client;
use Guzzle\Http\Message\RequestInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
     * @var DispersalStrategyInterface
     */
    private $strategy;

    /**
     * @var CacheStrategyInterface
     */
    private $cacheStrategy;
    
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
     */
    public function __construct(ValidatorInterface $validator, Client $client)
    {
        $this->validator = $validator;
        $this->client = $client;
    }

    /**
     * @inheritDoc
     *
     * @param DispersalStrategyInterface $strategy dispersal strategy
     *
     * @return void
     */
    public function setDispersalStrategy($strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * @inheritDoc
     *
     * @param CacheStrategyInterface $strategy cache strategy
     *
     * @return void
     */
    public function setCacheStrategy($strategy)
    {
        $this->cacheStrategy = $strategy;
    }

    /**
     * set curl options
     *
     * @param array $curlOptions the curl options
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
     * @param $request
     */
    protected function applyCurlOptions($request) {
        $curl = $request->getCurlOptions();
        foreach ($this->curlOptions as $option => $value) {
            $option = 'CURLOPT_' . strtoupper($option);
            $curl->set(constant($option), $value);
        }
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
        $retVal = null;
        if (isset($this->strategy)) {
            $request = $this->client->get($input);
            $this->applyCurlOptions($request);
            if (isset($this->cacheStrategy) && !$this->cacheStrategy->isExpired($this->options['storeKey'])) {
                $content = $this->cacheStrategy->get($this->options['storeKey']);

                if (empty($content)) {
                    $content = $this->fetchFile($request);
                }
            } else {
                $content = $this->fetchFile($request);
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
    private function fetchFile($request)
    {
        try {
            $response = $request->send();
        } catch (\Guzzle\Http\Exception\CurlException $e) {
            throw new HttpException(
                Response::HTTP_BAD_GATEWAY,
                $e->getError(),
                $e,
                $e->getRequest()->getHeaders()->toArray(),
                $e->getCode()
            );
        }
        $content = $response->getBody(true);
        if (isset($this->cacheStrategy)) {
            $this->cacheStrategy->save($this->options['storeKey'], $content);
        }

        return $content;
    }
}
