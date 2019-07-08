<?php
/**
 * handle graviton.rest.event events
 */
namespace Graviton\RestBundle\Subscriber;

use Graviton\RestBundle\Event\RestEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Subscriber for kernel.request and kernel.response events.
 * This class dispatches the graviton.rest.event event
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
final class RestEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var Response
     */
    private $response;

    /**
     * @var RestEvent
     */
    private $restEvent;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param Response           $response  Response
     * @param RestEvent          $restEvent Rest event
     * @param ContainerInterface $container Container
     */
    public function __construct(Response $response, RestEvent $restEvent, ContainerInterface $container)
    {
        $this->response = $response;
        $this->restEvent = $restEvent;
        $this->container = $container;
    }

    /**
     * Returns the subscribed events
     *
     * @return array $ret Events to subscribe
     */
    public static function getSubscribedEvents()
    {
        /**
         * There is a priority flag to manage execution order.
         * If we need more controll over this, we can use this flag or add
         * pre/post Methods like described here:
         * http://symfony.com/doc/current/components/event_dispatcher/introduction.html (StoreSubscriber)
         */

        return array(
            'kernel.request'  => array("onKernelRequest", 0),
            'kernel.response' => array("onKernelResponse", 0)
        );
    }

    /**
     * Handler for kernel.request events
     *
     * @param RequestEvent             $event      Event
     * @param string                   $name       Event name
     * @param EventDispatcherInterface $dispatcher Event dispatcher
     *
     * @return void
     */
    public function onKernelRequest(RequestEvent $event, $name, EventDispatcherInterface $dispatcher)
    {
        $restEvent = $this->getEventObject($event);
        if ($restEvent instanceof RestEvent) {
            $dispatcher->dispatch($restEvent, "graviton.rest.request");
        }
    }

    /**
     * Get a RestEvent object -> put this in a factory
     *
     * @param KernelEvent $event Original event (kernel.request / kernel.response)
     *
     * @return RestEvent $restEvent
     */
    private function getEventObject(KernelEvent $event)
    {
        // get the service name
        list ($serviceName) = explode(":", $event->getRequest()->get('_controller'));

        // get the controller which handles this request
        if ($this->container->has($serviceName)) {
            $controller = $this->container->get($serviceName);
            $restEvent = $this->restEvent;
            $restEvent->setRequest($event->getRequest());
            $restEvent->setResponse($this->response);
            $restEvent->setController($controller);

            $returnEvent = $restEvent;
        } else {
            $returnEvent = $event;
        }

        return $returnEvent;
    }

    /**
     * Handler for kernel.response events
     *
     * @param ResponseEvent            $event      Event
     * @param string                   $name       Event name
     * @param EventDispatcherInterface $dispatcher Event dispatcher
     *
     * @return void
     */
    public function onKernelResponse(ResponseEvent $event, $name, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->dispatch($event, "graviton.rest.response");
    }
}
