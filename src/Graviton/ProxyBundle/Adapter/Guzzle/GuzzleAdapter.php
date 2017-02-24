<?php
/**
 * Guzzle adapter class
 */

namespace Graviton\ProxyBundle\Adapter\Guzzle;

use GuzzleHttp\Client;
use Proxy\Adapter\AdapterInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 *
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
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
    private $options;


    /**
     * GuzzleAdapter constructor.
     *
     * @link https://gist.github.com/jseidl/3218673
     *
     * @param Client $client      guzzle client
     * @param array  $curlOptions List of curl options to be recognized for a request.
     */
    public function __construct(Client $client, array $curlOptions)
    {
        $this->client = $client;
        $this->options = $this->applyOptions($curlOptions);
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
        return $this->client->send($request, $this->options);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array  $options
     *
     * @return mixed|ResponseInterface
     */
    public function request($method, $uri = '', array $options = [])
    {
        // provide possibility to override default curlopts by passing $options.
        $options = array_merge($this->options, $options);

        return $this->client->request($method, $uri, $options);
    }

    /**
     * @return array
     */
    private function applyOptions($options)
    {
        $options = array('curl' => []);
        foreach ($options as $option => $value) {
            $options['curl'][constant('CURLOPT_' . strtoupper($option))] = $value;
        }
        $options['verify'] = __DIR__ . '/../../Resources/cert/cacert.pem';

        return $options;
    }


}
