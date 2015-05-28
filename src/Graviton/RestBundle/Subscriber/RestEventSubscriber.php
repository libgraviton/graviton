<?php
/**
 * handle graviton.rest.event events
 */
namespace Graviton\RestBundle\Subscriber;

use Graviton\RestBundle\Event\RestEvent;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Graviton\RestBundle\Listener\PagingLinkResponseListener;

/**
 * Subscriber for kernel.request and kernel.response events.
 * This class dispatches the graviton.rest.event event
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class RestEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var PagingLinkResponseListener
     */
    private $listener;

    /**
     * @var RestEvent
     */
    private $restEvent;

    /**
     * @param $container
     */
    public function __construct(PagingLinkResponseListener $listener, RestEvent $restEvent)
    {
        $this->listener = $listener;
        $this->restEvent = $restEvent;
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
     * @param GetResponseEvent         $event      Event
     * @param string                   $name       Event name
     * @param EventDispatcherInterface $dispatcher Event dispatcher
     *
     * @return void
     */
    public function onKernelRequest(GetResponseEvent $event, $name, EventDispatcherInterface $dispatcher)
    {
        $restEvent = $this->getEventObject($event);

        $dispatcher->dispatch("graviton.rest.request", $restEvent);
    }

    /**
     * Get a RestEvent object -> put this in a factory
     *
     * @param Event $event Original event (kernel.request / kernel.response)
     *
     * @return RestEvent $restEvent
     */
    private function getEventObject(Event $event)
    {
        // get the service name
        list ($serviceName) = explode(":", $event->getRequest()->get('_controller'));

        // get the controller which handles this request
        $controller = $this->container->get($serviceName);

        $restEvent = $this->restEvent;
        $restEvent->setRequest($event->getRequest());
        $restEvent->setResponse($this->listener);
        $restEvent->setController($controller);

        return $restEvent;
    }

    /**
     * Handler for kernel.response events
     *
     * @param FilterResponseEvent      $event      Event
     * @param string                   $name       Event name
     * @param EventDispatcherInterface $dispatcher Event dispatcher
     *
     * @return void
     */
    public function onKernelResponse(FilterResponseEvent $event, $name, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->dispatch("graviton.rest.response", $event);
    }
}
