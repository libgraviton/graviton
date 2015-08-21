<?php

namespace Graviton\MessageBundle\Event;

use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Exception;

class EventDispatcher extends ContainerAwareEventDispatcher
{

    protected $producer = null;

    public function dispatch($eventName, Event $event = null)
    {
        parent::dispatch($eventName, $event);
        $message = array(
            'event' => $event,
        );
        $producer = $this->getProducer();
        $producer->setContentType('application/json');
        $producer->publish(json_encode($message), $eventName);
        var_dump($eventName);

    }

    public function setProducer(Producer $producer)
    {
        $this->producer = $producer;
    }

    public function getProducer()
    {
        if (! $this->producer instanceof Producer) {
            throw new Exception(
                'Producer not set. Call setProducer() first.'
            );
        }
        return $this->producer;
    }
}