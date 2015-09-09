<?php
/**
 *
 */

namespace Graviton\ProxyBundle\Controller;


use Guzzle\Service\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProxyController
{
    /*
     * @var ClientInterface
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function proxyAction(Request $request)
    {
        $requestInfo = new \stdClass();
        $requestInfo->url = $request->getUri();
        $requestInfo->host = $request->getHost();
        $requestInfo->method = $request->getMethod();

        return new Response(json_encode($requestInfo), 200);
    }
}
