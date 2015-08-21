<?php

namespace Graviton\MessageBundle\Event;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use JMS\Serializer\Serializer;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class DoctrineEventSubscriber implements EventSubscriber
{

    protected $serializer = null;

    protected $rabbitMqProducer = null;

    public function __construct(
        Serializer $serializer,
        Producer $rabbitMqProducer
    ) {
        $this->serializer = $serializer;
        $this->rabbitMqProducer = $rabbitMqProducer;
    }

    public function getSubscribedEvents()
    {
        return array(
            'postPersist',
            'postUpdate',
            'postRemove',
        );
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->publishMessage($args->getDocument(), 'create');
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
          $this->publishMessage($args->getDocument(), 'update');

    }

    public function postRemove(LifecycleEventArgs $args)
    {
          $this->publishMessage($args->getDocument(), 'delete');

    }

    public function publishMessage($document, $event)
    {
        $routingKey = $this->toResourceName($document) . '.' . $event;
        $this->rabbitMqProducer->publish($this->serializeDocument($document), $routingKey);
    }

    protected function toResourceName($document){
        return !is_scalar($document) ? get_class($document) : $document;
    }

    protected function serializeDocument($document)
    {
        try {
            return $this->serializer->serialize($document, 'json');
        } catch (\Exception $e) {
            return '{}';
        }
    }
}