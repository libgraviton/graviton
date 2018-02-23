<?php
/**
 * A listener that globally modifies the Router Context if configured
 */

namespace Graviton\CoreBundle\Listener;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RequestHostListener
{

    /**
     * router
     *
     * @var Router
     */
    private $router;

    /**
     * host
     *
     * @var string
     */
    private $host;

    /**
     * configured port for http
     *
     * @var int
     */
    private $portHttp;

    /**
     * configured port for https
     *
     * @var int
     */
    private $portHttps;

    /**
     * constructor
     *
     * @param Router $router    router
     * @param string $host      host
     * @param int    $portHttp  port for http
     * @param int    $portHttps port for https
     */
    public function __construct(Router $router, $host, $portHttp, $portHttps)
    {
        $this->router = $router;
        $this->host = $host;
        $this->portHttp = $portHttp;
        $this->portHttps = $portHttps;
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
