<?php
/**
 * A listener that globally modifies the Router Context if configured
 */

namespace Graviton\CoreBundle\Listener;

use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
readonly class RequestHostListener
{

    /**
     * constructor
     *
     * @param Router  $router    router
     * @param ?string $host      host
     * @param ?int    $portHttp  port for http
     * @param ?int    $portHttps port for https
     */
    public function __construct(private Router $router, private ?string $host, private ?int $portHttp, private ?int $portHttps)
    {
    }

    /**
     * modify the router context params if configured
     *
     * @return void
     */
    public function onKernelRequest()
    {
        if (!is_null($this->host)) {
            $this->router->getContext()->setHost($this->host);
        }

        if (!is_null($this->portHttp)) {
            $this->router->getContext()->setHttpPort($this->portHttp);
        }

        if (!is_null($this->portHttps)) {
            $this->router->getContext()->setHttpsPort($this->portHttps);
        }
    }
}
