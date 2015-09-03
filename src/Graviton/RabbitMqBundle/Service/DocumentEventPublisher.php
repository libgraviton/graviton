<?php

/**
 * Publishes document level messages to the messaging bus.
 */

namespace Graviton\RabbitMqBundle\Service;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Graviton\RabbitMqBundle\Document\QueueEvent;
use Monolog\Logger;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Publishes document level messages to the messaging bus and creates a new JobStatus Document.
 * Moreover, this class can be used as a Doctrine Event Subscriber to automatically publish the postPersist,
 * postUpdate and postRemove events.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
final class DocumentEventPublisher implements EventSubscriber
{

    /**
     * @var ProducerInterface Producer for publishing messages.
     */
    private $rabbitMqProducer = null;

    /**
     * @var Logger Logger
     */
    private $logger = null;

    /**
     * @var RouterInterface Router to generate resource URLs
     */
    private $router = null;

    /**
     * @var array mapping from class shortname ("collection") to controller service
     */
    private $documentMapping = array();

    /**
     * @var QueueEvent queueevent document
     */
    private $queueEventDocument;

    /**
     * @var string classname of the EventWorker document
     */
    private $eventWorkerClassname;

    /**
     * @var string classname of the EventStatus document
     */
    private $eventStatusClassname;

    /**
     * @param ProducerInterface $rabbitMqProducer     RabbitMQ dependency
     * @param LoggerInterface   $logger               Logger dependency
     * @param RouterInterface   $router               Router dependency
     * @param QueueEvent        $queueEventDocument   queueevent document
     * @param array             $documentMapping      document mapping
     * @param string            $eventWorkerClassname classname of the EventWorker document
     * @param string            $eventStatusClassname classname of the EventStatus document
     */
    public function __construct(
        ProducerInterface $rabbitMqProducer,
        LoggerInterface $logger,
        RouterInterface $router,
        QueueEvent $queueEventDocument,
        array $documentMapping,
        $eventWorkerClassname,
        $eventStatusClassname
    ) {
        $this->rabbitMqProducer = $rabbitMqProducer;
        $this->logger = $logger;
        $this->router = $router;
        $this->queueEventDocument = $queueEventDocument;
        $this->documentMapping = $documentMapping;
        $this->eventWorkerClassname = $eventWorkerClassname;
        $this->eventStatusClassname = $eventStatusClassname;
    }

    /**
     * @return array Defines the doctrine events to subscribe to.
     */
    public function getSubscribedEvents()
    {
        return array(
            'postPersist',
            'postUpdate',
            'postRemove',
        );
    }

    /**
     * Doctrine postPersist event listener
     *
     * @param LifecycleEventArgs $args Event Arguments
     *
     * @return void
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->publishEvent($args, 'create');
    }

    /**
     * Doctrine postUpdate event listener
     *
     * @param LifecycleEventArgs $args Event Arguments
     *
     * @return void
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->publishEvent($args, 'update');
    }

    /**
     * Doctrine postRemove event listener
     *
     * @param LifecycleEventArgs $args Event Arguments
     *
     * @return void
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $this->publishEvent($args, 'delete');
    }

    /**
     * Returns whether our needed Model classes are currently available or not
     *
     * @return bool true if yes, false if not
     */
    private function isPublishableContext()
    {
        return (class_exists($this->eventWorkerClassname) && class_exists($this->eventStatusClassname));
    }

    /**
     * Creates a new JobStatus document. Then publishes it's id with a message onto the message bus.
     * The message and routing key get determined by a given document and an action name.
     *
     * @param LifecycleEventArgs $args  Event Arguments
     * @param string             $event The action name
     *
     * @return void
     */
    private function publishEvent(LifecycleEventArgs $args, $event)
    {
        $queueObject = $this->createQueueEventObject($args->getDocument(), $event);
        $workerIds = $this->getSubscribedWorkerIds($args, $queueObject);

        $this->rabbitMqProducer->publish(
            json_encode($queueObject),
            $queueObject->getRoutingKey()
        );
    }

    /**
     * Created the structured object that will be sent to the queue
     *
     * @param object $document The document for determining message and routing key
     * @param string $event    What type of event
     *
     * @return \stdClass
     */
    private function createQueueEventObject($document, $event)
    {
        $obj = clone $this->queueEventDocument;
        $obj->setClassname(get_class($document));
        $obj->setRecordid($document->getId());
        $obj->setEvent($event);

        // get the public facing url (if available)
        $documentClass = new \ReflectionClass($document);
        $shortName = $documentClass->getShortName();

        if (isset($this->documentMapping[$shortName])) {
            $obj->setPublicurl(
                $this->router->generate(
                    $this->documentMapping[$shortName] . '.get',
                    ['id' => $document->getId()],
                    true
                )
            );
        }

        // compose routing key
        // here, we're generating something arbitrary that is properly topic based (namespaced)
        $baseKey = str_replace('\\', '.', strtolower($obj->getClassname()));
        list(, $bundle, , $document) = explode('.', $baseKey);

        // will be ie. 'document.core.app.create' for /core/app creation
        $routingKey = 'document.'.
            str_replace('bundle', '', $bundle).
            '.'.
            $document.
            '.'.
            $event;

        $obj->setRoutingKey($routingKey);

        return $obj;
    }

    /**
     * Checks EventWorker for worker that are subscribed to our event and returns
     * their workerIds as array
     *
     * @param LifecycleEventArgs $args       doctrine event args
     * @param QueueEvent         $queueEvent queueevent object
     *
     * @return array array of worker ids
     */
    private function getSubscribedWorkerIds(LifecycleEventArgs $args, QueueEvent $queueEvent)
    {
        // compose our regex to match stars ;-)
        // results in = /([\*|document]+)\.([\*|dude]+)\.([\*|config]+)\.([\*|update]+)/
        $routingArgs = explode('.', $queueEvent->getRoutingKey());
        $regex =
            '/'.
            implode(
                '\.',
                array_map(
                    function ($arg) {
                        return '([\*|'.$arg.']+)';
                    },
                    $routingArgs
                )
            ).
            '/';

        $dm = $args->getDocumentManager()->createQueryBuilder($this->eventWorkerClassname);

        $data = $dm
            ->select('id')
            ->field('subscription.event')
            ->equals(new \MongoRegex($regex))
            ->getQuery()
            ->execute()->toArray();

        return array_keys($data);
    }
}
