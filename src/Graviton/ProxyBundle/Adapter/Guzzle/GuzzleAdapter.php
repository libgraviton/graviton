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
        $options = array('curl' => []);
        foreach ($this->curlOptions as $option => $value) {
            $options['curl'][constant('CURLOPT_'.strtoupper($option))] = $value;
        }
        $options['verify'] = __DIR__.'/../../Resources/cert/cacert.pem';

        return $this->client->send($request, $options);
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
