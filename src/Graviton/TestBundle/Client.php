<?php

namespace Graviton\TestBundle;

use Symfony\Bundle\FrameworkBundle\Client as FrameworkClient;

class Client extends FrameworkClient
{
    /**
     * @var String
     */
    private $results;

    /**
     * return decoded results from a request
     *
     * @return object
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * POSTs to an URI.
     *
     * @param string  $uri           The URI to fetch
     * @param string  $content       The raw body data
     * @param array   $parameters    The Request parameters
     * @param array   $files         The files
     * @param array   $server        The server parameters (HTTP headers are referenced with a HTTP_ prefix as PHP does)
     * @param bool    $changeHistory Whether to update the history or not (only used internally for back(), forward(), and reload())
     *
     * @return Crawler
     *
     * @api
     */
    public function post($uri, $content, array $parameters = array(), array $files = array(), array $server = array(), $changeHistory = true)
    {
        return $this->request('POST', $uri, $parameters, $files, $server, json_encode($content), $changeHistory);
    }

    /**
     * PUTs to an URI.
     *
     * @param string  $uri           The URI to fetch
     * @param string  $content       The raw body data
     * @param array   $parameters    The Request parameters
     * @param array   $files         The files
     * @param array   $server        The server parameters (HTTP headers are referenced with a HTTP_ prefix as PHP does)
     * @param bool    $changeHistory Whether to update the history or not (only used internally for back(), forward(), and reload())
     *
     * @return Crawler
     *
     * @api
     */
    public function put($uri, $content, array $parameters = array(), array $files = array(), array $server = array(), $changeHistory = true)
    {
        return $this->request('PUT', $uri, $parameters, $files, $server, json_encode($content), $changeHistory);
    }

    /**
     * prepare a deserialized copy of a json response
     *
     * @param object $response Response containing our return value as raw json
     *
     * @return object response
     *
     * @todo use JMSSerializer for additional JSON validation
     */
    protected function filterResponse($response)
    {
        $this->results = json_decode($response->getContent());
        return parent::filterResponse($response);
    }

    /**
     * force all requests to be json like.
     *
     * Always do JSON/RESTful requests using this client. Use the parent Client
     * if you want to make any other kind of requests!
     *
     * @param object $request Request object
     *
     * @return object request
     */
    protected function doRequest($request)
    {
        $request->headers->set('Content-Type', 'application/json; charset=UTF-8');
        $request->headers->set('Accept', 'application/json');

        return parent::doRequest($request);
    }
}
