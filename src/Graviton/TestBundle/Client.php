<?php
/**
 * Client helper for RESTful tests.
 */

namespace Graviton\TestBundle;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * Client containing some helper methods to be RESTful.
 *
 * This is mainly used during acceptance testing.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class Client
{

    /**
     * @var KernelBrowser
     */
    private $client;

    /**
     * @var boolean
     */
    private $jsonRequest = true;

    /**
     * Client constructor.
     *
     * @param KernelBrowser $client client
     */
    public function __construct(KernelBrowser $client)
    {
        $this->client = $client;
    }

    /**
     * return decoded results from a request
     *
     * @return mixed
     */
    public function getResults()
    {
        return json_decode($this->client->getResponse()->getContent());
    }

    /**
     * POSTs to an URI.
     *
     * @param string  $uri        The URI to fetch
     * @param mixed   $content    The raw body data
     * @param array   $parameters The Request parameters
     * @param array   $files      The files
     * @param array   $server     The server parameters (HTTP headers are referenced with a HTTP_ prefix as PHP does)
     * @param boolean $jsonEncode If $content should be json encoded or not
     *
     * @return \Symfony\Component\DomCrawler\Crawler|null
     *
     * @api
     */
    public function post(
        $uri,
        $content,
        array $parameters = [],
        array $files = [],
        array $server = [],
        $jsonEncode = true
    ) {
        $this->jsonRequest = $jsonEncode;

        if ($jsonEncode) {
            $content = json_encode($content);
        }

        return $this->client->request(
            'POST',
            $uri,
            $parameters,
            $files,
            $server,
            $content
        );
    }

    /**
     * PUTs to an URI.
     *
     * @param string  $uri        The URI to fetch
     * @param mixed   $content    The raw body data
     * @param array   $parameters The Request parameters
     * @param array   $files      The files
     * @param array   $server     The server parameters (HTTP headers are referenced with a HTTP_ prefix as PHP does)
     * @param boolean $jsonEncode If $content should be json encoded or not
     *
     * @return \Symfony\Component\DomCrawler\Crawler|null
     *
     * @api
     */
    public function put(
        $uri,
        $content,
        array $parameters = [],
        array $files = [],
        array $server = [],
        $jsonEncode = true
    ) {
        $this->jsonRequest = $jsonEncode;

        if ($jsonEncode) {
            $content = json_encode($content);
        }

        return $this->client->request(
            'PUT',
            $uri,
            $parameters,
            $files,
            $server,
            $content
        );
    }

    /**
     * magic function for KernelBrowser functions
     *
     * @param string $name      function name
     * @param array  $arguments params
     *
     * @return mixed return
     */
    public function __call(string $name, array $arguments)
    {
        return call_user_func_array([$this->client, $name], $arguments);
    }
}
