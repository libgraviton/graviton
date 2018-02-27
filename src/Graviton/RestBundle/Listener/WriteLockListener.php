<?php
/**
 * listener that implements write locks on data altering requests with PUT and PATCH methods.
 */

namespace Graviton\RestBundle\Listener;

use Monolog\Logger;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\SemaphoreStore;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Graviton\RestBundle\HttpFoundation\LinkHeader;
use Graviton\RestBundle\HttpFoundation\LinkHeaderItem;
use Graviton\RestBundle\Event\RestEvent;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class WriteLockListener
{

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var RequestStack
     */
    private $requestStack;

    private $lockingMethods = [
        Request::METHOD_PUT,
        Request::METHOD_PATCH
    ];

    /**
     * @param Router $router router
     */
    public function __construct(Logger $logger, RequestStack $requestStack)
    {
        $this->logger = $logger;
        $this->requestStack = $requestStack;

        $store = new SemaphoreStore();
        $this->factory = new Factory($store);
    }

    /**
     * add a rel=self Link header to the response
     *
     * @param FilterResponseEvent      $event      response listener event
     * @param string                   $eventName  event name
     * @param EventDispatcherInterface $dispatcher dispatcher
     *
     * @return void
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (!in_array($this->requestStack->getCurrentRequest()->getMethod(), $this->lockingMethods)) {
            return;
        }

        $url = $this->requestStack->getCurrentRequest()->getPathInfo();
        $lock = $this->factory->createLock($url);
        var_dump($event->getController());


        echo "dude"; die;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $url = $this->requestStack->getCurrentRequest()->getPathInfo();
    }

}
