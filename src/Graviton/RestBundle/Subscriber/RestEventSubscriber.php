<?php
namespace Graviton\RestBundle\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\Event;
use Graviton\RestBundle\Event\RestEvent;

/**
 * Subscriber for kernel.request and kernel.response events.
 * This class dispatches the graviton.rest.event event
 *
 * @category RestBundle
 * @package  Graviton
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.com
 */
class RestEventSubscriber implements EventSubscriberInterface
{
    /**
     * DI container
     *
     * @var Symfony\Component\DependencyInjection\Container
     */
    private $container;

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
            'kernel.request' => array("onKernelRequest", 0),
            'kernel.response' => array("onKernelResponse", 0)
        );
    }

    /**
     * Handler for kernel.request events
     *
     * @param GetResponseEvent $event Event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $dispatcher = $event->getDispatcher();
        $restEvent = $this->getEventObject($event);

        $dispatcher->dispatch("graviton.rest.request", $restEvent);
    }

    /**
     * Handler for kernel.response events
     *
     * @param FilterResponseEvent $event Event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $dispatcher = $event->getDispatcher();
        $restEvent = $this->getEventObject($event);

        $dispatcher->dispatch("graviton.rest.response", $restEvent);

        // setResponse stops the propagation of this event
        $event->setResponse($restEvent->getResponse());
    }

    /**
     * Set the di container
     *
     * @param Symfony\Component\DependencyInjection\Container $container DI container
     *
     * @return \Graviton\RestBundle\Subscriber\RestEventSubscriber $this This object
     */
    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the di container
     *
     * @return \Graviton\RestBundle\Subscriber\Symfony\Component\DependencyInjection\Container $container DI container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Get a RestEvent object -> put this in a factory
     *
     * @param Event $event Original event (kernel.request / kernel.response)
     *
     * @return RestEvent $evet
     */
    private function getEventObject(Event $event)
    {
        $response = $this->container->get("graviton.rest.response");

        // get the service name
        list ($serviceName, $action) = explode(":", $event->getRequest()->get('_controller'));

        // get the controller which handles this request
        $controller = $this->container->get($serviceName);

        $restEvent = $this->container->get("graviton.rest.event");
        $restEvent->setRequest($event->getRequest());
        $restEvent->setResponse($response);
        $restEvent->setController($controller);

        return $restEvent;
    }
}
