<?php

/**
 * Publishes document level messages to the messaging bus.
 */

namespace Graviton\RabbitMqBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Graviton\RabbitMqBundle\Document\QueueEvent;
use Monolog\Logger;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

/**
 * Publishes document level messages to the messaging bus and creates a new EventStatus Document.
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
     * @var RequestStack request stack
     */
    private $requestStack;

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
     * @var string route name of the /event/status route
     */
    private $eventStatusRouteName;

    /**
     * @param ProducerInterface $rabbitMqProducer           RabbitMQ dependency
     * @param LoggerInterface   $logger                     Logger dependency
     * @param RouterInterface   $router                     Router dependency
     * @param RequestStack      $requestStack               Request stack
     * @param QueueEvent        $queueEventDocument         queueevent document
     * @param array             $documentMapping            document mapping
     * @param string            $eventWorkerClassname       classname of the EventWorker document
     * @param string            $eventStatusClassname       classname of the EventStatus document
     * @param string            $eventStatusStatusClassname classname of the EventStatusStatus document
     * @param string            $eventStatusRouteName       name of the route to EventStatus
     */
    public function __construct(
        ProducerInterface $rabbitMqProducer,
        LoggerInterface $logger,
        RouterInterface $router,
        RequestStack $requestStack,
        QueueEvent $queueEventDocument,
        array $documentMapping,
        $eventWorkerClassname,
        $eventStatusClassname,
        $eventStatusStatusClassname,
        $eventStatusRouteName
    ) {
        $this->rabbitMqProducer = $rabbitMqProducer;
        $this->logger = $logger;
        $this->router = $router;
        $this->requestStack = $requestStack;
        $this->queueEventDocument = $queueEventDocument;
        $this->documentMapping = $documentMapping;
        $this->eventWorkerClassname = $eventWorkerClassname;
        $this->eventStatusClassname = $eventStatusClassname;
        $this->eventStatusStatusClassname = $eventStatusStatusClassname;
        $this->eventStatusRouteName = $eventStatusRouteName;
    }

    /**
     * returns subscribed events
     *
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
        $queueObject = $this->createQueueEventObject($args, $event);

        // should we set QueueEvent in request attributes for our Link header Listener?
        if ($this->requestStack->getCurrentRequest() instanceof Request &&
            !is_null($queueObject->getStatusurl())
        ) {
            $this->requestStack->getCurrentRequest()->attributes->set('eventStatus', $queueObject);
        }

        $this->rabbitMqProducer->publish(
            json_encode($queueObject),
            $queueObject->getRoutingKey()
        );
    }

    /**
     * Creates the structured object that will be sent to the queue
     *
     * @param LifecycleEventArgs $args  doctrine event args
     * @param string             $event What type of event
     *
     * @return \stdClass
     */
    private function createQueueEventObject(LifecycleEventArgs $args, $event)
    {
        $obj = clone $this->queueEventDocument;
        $obj->setClassname(get_class($args->getDocument()));
        $obj->setRecordid($args->getDocument()->getId());
        $obj->setEvent($event);
        $obj->setPublicurl($this->getPublicResourceUrl($args));
        $obj->setRoutingKey($this->generateRoutingKey($args, $event));
        $obj->setStatusurl($this->getStatusUrl($args, $obj));
        return $obj;
    }

    /**
     * compose our routingKey. this will have the form of 'document.[bundle].[document].[event]'
     * rules:
     *  * always 4 parts divided by points.
     *  * in this context (doctrine/odm stuff) we prefix with 'document.'
     *
     * @param LifecycleEventArgs $args  doctrine event args
     * @param string             $event What type of event
     *
     * @return string routing key
     */
    private function generateRoutingKey(LifecycleEventArgs $args, $event)
    {
        $baseKey = str_replace('\\', '.', strtolower(get_class($args->getDocument())));
        list(, $bundle, , $document) = explode('.', $baseKey);

        // will be ie. 'document.core.app.create' for /core/app creation
        $routingKey = 'document.'.
            str_replace('bundle', '', $bundle).
            '.'.
            $document.
            '.'.
            $event;

        return $routingKey;
    }

    /**
     * get public url to our affected resource
     *
     * @param LifecycleEventArgs $args doctrine event args
     *
     * @return string url
     */
    private function getPublicResourceUrl(LifecycleEventArgs $args)
    {
        $documentClass = new \ReflectionClass($args->getDocument());
        $shortName = $documentClass->getShortName();
        $url = null;

        if (isset($this->documentMapping[$shortName])) {
            $url = $this->router->generate(
                $this->documentMapping[$shortName] . '.get',
                [
                    'id' => $args->getDocument()
                                 ->getId()
                ],
                true
            );
        }

        return $url;
    }

    /**
     * Creates a EventStatus object that gets persisted..
     *
     * @param LifecycleEventArgs $args       doctrine event args
     * @param QueueEvent         $queueEvent queueevent object
     *
     * @return array array of worker ids
     */
    private function getStatusUrl(LifecycleEventArgs $args, QueueEvent $queueEvent)
    {
        $workerIds = $this->getSubscribedWorkerIds($args, $queueEvent);
        if (empty($workerIds)) {
            return null;
        }

        // we have subscribers; create the EventStatus entry
        $eventStatus = new $this->eventStatusClassname();
        $eventStatus->setCreatedate(new \DateTime());

        foreach ($workerIds as $workerId) {
            $eventStatusStatus = new $this->eventStatusStatusClassname();
            $eventStatusStatus->setWorkerid($workerId);
            $eventStatusStatus->setStatus('opened');
            $eventStatus->addStatus($eventStatusStatus);
        }

        $args->getDocumentManager()->persist($eventStatus);
        $args->getDocumentManager()->flush();

        // get the url..
        $url = $this->router->generate(
            $this->eventStatusRouteName,
            [
                'id' => $eventStatus->getId()
            ],
            true
        );

        return $url;
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
