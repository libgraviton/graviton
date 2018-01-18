<?php
/**
 * Guzzle adapter class
 */

namespace Graviton\ProxyBundle\Adapter\Guzzle;

use GuzzleHttp\Client;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 *
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class GuzzleAdapter extends Client
{

    /**
     * curl options
     *
     * @var array
     */
    private $curlOptions;

    /**
     * @inheritDoc
     *
     * @param RequestInterface $request request
     * @param array            $options options
     *
     * @return ResponseInterface
     */
    public function send(RequestInterface $request, array $options = [])
    {
        $opt = array('curl' => []);
        foreach ($this->curlOptions as $option => $value) {
            $opt['curl'][constant('CURLOPT_'.strtoupper($option))] = $value;
        }
        $opt['verify'] = __DIR__.'/../../Resources/cert/cacert.pem';
        return parent::send($request, array_merge($options, $opt));
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
