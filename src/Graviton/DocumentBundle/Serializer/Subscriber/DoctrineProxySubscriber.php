<?php
/**
 * doctrine proxy subscriber
 * solves the problem how we serialize proxy-manager proxy objects using doctrine odm.
 */

namespace Graviton\DocumentBundle\Serializer\Subscriber;

use JMS\Serializer\EventDispatcher\EventDispatcherInterface;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use ProxyManager\Proxy\ProxyInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class DoctrineProxySubscriber implements EventSubscriberInterface
{
    /**
     * @var bool
     */
    private $skipVirtualTypeInit = false;

    /**
     * @var bool
     */
    private $initializeExcluded = false;

    /**
     * pre serialize event
     *
     * @param PreSerializeEvent $event event
     *
     * @return void
     */
    public function onPreSerialize(PreSerializeEvent $event)
    {
        $object = $event->getObject();
        $type = $event->getType();
        $virtualType = !class_exists($type['name'], false);

        if (($this->skipVirtualTypeInit && $virtualType) ||
            (!$object instanceof ProxyInterface)
        ) {
            return;
        }

        // do not initialize the proxy if is going to be excluded by-class by some exclusion strategy
        if ($this->initializeExcluded === false && !$virtualType) {
            $context = $event->getContext();
            $exclusionStrategy = $context->getExclusionStrategy();
            if ($exclusionStrategy !== null &&
                $exclusionStrategy->shouldSkipClass(
                    $context->getMetadataFactory()->getMetadataForClass(get_parent_class($object)),
                    $context
                )
            ) {
                return;
            }
        }

        if (!$virtualType) {
            $event->setType(get_parent_class($object), $type['params']);
        }
    }

    /**
     * serialized proxy type
     *
     * @param PreSerializeEvent        $event      event
     * @param string                   $eventName  event name
     * @param string                   $class      class
     * @param string                   $format     format
     * @param EventDispatcherInterface $dispatcher dispatcher
     *
     * @return void
     */
    public function onPreSerializeTypedProxy(
        PreSerializeEvent $event,
        $eventName,
        $class,
        $format,
        EventDispatcherInterface $dispatcher
    ) {
        $type = $event->getType();
        // is a virtual type? then there is no need to change the event name
        if (!class_exists($type['name'], false)) {
            return;
        }

        $object = $event->getObject();
        if ($object instanceof ProxyInterface) {
            $parentClassName = get_parent_class($object);

            // check if this is already a re-dispatch
            if (strtolower($class) !== strtolower($parentClassName)) {
                $event->stopPropagation();
                $newEvent = new PreSerializeEvent(
                    $event->getContext(),
                    $object,
                    ['name' => $parentClassName, 'params' => $type['params']]
                );
                $dispatcher->dispatch($eventName, $parentClassName, $format, $newEvent);

                // update the type in case some listener changed it
                $newType = $newEvent->getType();
                $event->setType($newType['name'], $newType['params']);
            }
        }
    }

    /**
     * gets subscribed events
     *
     * @return array events
     */
    public static function getSubscribedEvents()
    {
        return [
            ['event' => 'serializer.pre_serialize', 'method' => 'onPreSerializeTypedProxy'],
            ['event' => 'serializer.pre_serialize', 'method' => 'onPreSerialize'],
        ];
    }
}
