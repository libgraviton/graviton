<?php
/**
 * Http request helper, to make http client in one place.
 */

namespace Graviton\ProxyApiBundle\Helper;

use Graviton\ProxyApiBundle\Listener\ProxyExceptionListener;
use GuzzleHttp\Client as httpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Psr7\Response as ClientResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Request manager and service definition start up
 * Build by compiler to be used in ProxyManager
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class HttpHelper
{
    protected $options = [];
    protected $requestUri = '';
    protected $requestMethod = 'GET';

    /**
     * HttpHelper for Guzzle operations.
     *
     * @param httpClient $httpClient Sf Request information service
     */
    public function __construct(
        httpClient $httpClient
    ) {
        $this->httpClient = $httpClient;
    }

    /**
     * @param string $uri Remote server Base url
     * @return void
     */
    public function setBaseUri($uri)
    {
        $this->options['base_uri'] = $uri;
    }

    /**
     * @param string $uri Remote server Base url
     * @return void
     */
    public function setRequestUri($uri)
    {
        $this->requestUri = $uri;
    }

    /**
     * @param string $method GET | POST | ...
     * @return void
     */
    public function setMethod($method)
    {
        $this->requestMethod = $method;
    }

    /**
     * Client request header
     *
     * @param string $name  key field
     * @param string $value assignation
     * @return void
     */
    public function addHeader($name, $value)
    {
        if (!array_key_exists('headers', $this->options)) {
            $this->options['headers'] = [];
        }
        $this->options['headers'] = [$name => $value];
    }

    /**
     * Client request query params
     *
     * @param string $name  key field
     * @param string $value assignation
     * @return void
     */
    public function addQueryParams($name, $value)
    {
        if (!array_key_exists('query', $this->options)) {
            $this->options['query'] = [];
        }
        $this->options['query'][$name] = $value;
    }

    /**
     * Run request against server
     *
     * @return Response
     */
    public function execute()
    {
        try {
            /** @var ClientResponse $httpResponse */
            $httpResponse = $this->httpClient->request(
                $this->requestMethod,
                $this->requestUri,
                $this->options
            );
        } catch (ClientException $e) {
            $status = $e->getResponse()->getStatusCode();
            throw new ProxyExceptionListener($status, 'Could not execute request, rejected by server.');
        } catch (\Exception $e) {
            throw new ProxyExceptionListener(400, 'Could not execute request, failure.');
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $headers = $httpResponse->getHeaders();
        $httpResponse->getStatusCode();

        // Make response with correct content headers
        if (array_key_exists('Content-Type', $headers)) {
            $response->headers->set('Content-Type', $headers['Content-Type']);
        }
        $response->setContent($httpResponse->getBody());
        $response->setStatusCode($httpResponse->getStatusCode());

        return $response;
    }
}
