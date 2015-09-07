<?php
/**
 * Created by PhpStorm.
 * User: samuel
 * Date: 07.09.15
 * Time: 08:24
 */

namespace Graviton\RestBundle\Controller;


use Guzzle\Service\Client;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class ProxyController extends RestController
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getAction(Request $request, $id)
    {
        //$request->ba
        //$this->client->get()
        /*$redirectResponse = new RedirectResponse("http://symfony.com/doc", 301);
        $redirectResponse->
        //$redirectResponse->send();

        return $redirectResponse;*/
    }
}