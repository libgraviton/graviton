<?php
/**
 * Guzzle adapter class
 */

namespace Graviton\ProxyBundle\Adapter\Guzzle;

use Guzzle\Service\Client;
use Proxy\Adapter\AdapterInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleAdapter implements AdapterInterface
{
    /**
     * Guzzle client
     *
     * @var Client
     */
    private $client;

    /**
     * curl options
     *
     * @var array
     */
    private $curlOptions;
    /**
     * constructor
     *
     * @param Client $client guzzle client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritDoc
     *
     * @param RequestInterface $request request
     *
     * @return ResponseInterface
     */
    public function send(RequestInterface $request)
    {
        $request = array($request);
        $this->client->setSslVerification();

        return $this->client->send($request);
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
}
