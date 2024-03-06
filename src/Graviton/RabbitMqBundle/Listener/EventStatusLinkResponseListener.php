<?php
/**
 * Response listener that adds an eventStatus to Link header if necessary, creates an EventStatus resource
 * and publishes the change to the queue
 */

namespace Graviton\RabbitMqBundle\Listener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Graviton\DocumentBundle\Service\ExtReferenceConverter;
use Graviton\LinkHeaderParser\LinkHeader;
use Graviton\LinkHeaderParser\LinkHeaderItem;
use Graviton\RabbitMqBundle\Entity\QueueEvent;
use Graviton\RabbitMqBundle\Producer\ProducerInterface;
use Graviton\RestBundle\Event\EntityPrePersistEvent;
use Graviton\RestBundle\Event\ModelEvent;
use Laminas\Diactoros\Uri;
use MongoDB\BSON\Regex;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Graviton\SecurityBundle\Service\SecurityUtils;
use GravitonDyn\EventStatusBundle\Document\EventStatus;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class EventStatusLinkResponseListener implements EventSubscriberInterface
{

    /**
     * @param Logger                   $logger                            logger
     * @param ProducerInterface        $rabbitMqProducer                  RabbitMQ dependency
     * @param RouterInterface          $router                            Router dependency
     * @param DocumentManager          $documentManager                   Doctrine document manager
     * @param EventDispatcherInterface $eventDispatcher                   event dispatcher
     * @param ExtReferenceConverter    $extRefConverter                   instance of the ExtReferenceConverter service
     * @param string                   $eventWorkerClassname              classname of the EventWorker document
     * @param string                   $eventStatusClassname              classname of the EventStatus document
     * @param string                   $eventStatusStatusClassname        classname of the EventStatusStatus document
     * @param string                   $eventStatusEventResourceClassname classname of the E*S*E*Resource document
     * @param string                   $eventStatusRouteName              name of the route to EventStatus
     * @param SecurityUtils            $securityUtils                     Security utils service
     * @param string                   $workerRelativeUrl                 backend url relative from the workers
     * @param array                    $transientHeaders                  headers to be included from request in event
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ProducerInterface $rabbitMqProducer,
        private readonly RouterInterface $router,
        private readonly DocumentManager $documentManager,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ExtReferenceConverter $extRefConverter,
        private readonly string $eventWorkerClassname,
        private readonly string $eventStatusClassname,
        private readonly string $eventStatusStatusClassname,
        private readonly string $eventStatusEventResourceClassname,
        private readonly string $eventStatusRouteName,
        private readonly SecurityUtils $securityUtils,
        private readonly ?string $workerRelativeUrl,
        private readonly array $transientHeaders,
        private array $queueToSend = []
    ) {
    }

    #[\Override] public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return [
            KernelEvents::TERMINATE => [
                ['onKernelTerminate', 0]
            ],
            ModelEvent::MODEL_EVENT_INSERT => [
                ['onModelEvent', 0]
            ],
            ModelEvent::MODEL_EVENT_UPDATE => [
                ['onModelEvent', 0]
            ],
            ModelEvent::MODEL_EVENT_DELETE => [
                ['onModelEvent', 0]
            ]
        ];
    }

    /**
     * add a rel=eventStatus Link header to the response if necessary
     *
     * @param ModelEvent $event response listener event
     *
     * @return void
     */
    public function onModelEvent(ModelEvent $event)
    {
        // what is the document event name?
        $eventName = $this->getDocumentEventName($event);

        // any worker subscribed?
        $workerIds = $this->getSubscribedWorkerIds($eventName);
        if (empty($workerIds)) {
            return;
        }

        $this->logger->info(
            sprintf("Found '%s' worker(s) subscribed for event '%s', will notify on queue.", count($workerIds), $eventName),
            ['workerIds' => $workerIds]
        );

        // the url to the resource that caused this EventStatus
        $documentUrl = $this->getEventRefUrl($event);

        // now, create EventStatus and get it's url
        $eventStatusUrl = $this->getStatusUrl($event, $eventName, $documentUrl, $workerIds);
        // set on request!
        if (!is_null($event->getRequest())) {
            $event->getRequest()->attributes->set('eventStatus', $eventStatusUrl);
        }

        // create the QueueEvent object
        $queueEvent = $this->createQueueEventObject($event, $eventName, $documentUrl, $eventStatusUrl);

        // put stuff in instance so we can send it in onTerminate
        $this->queueToSend[] = [$queueEvent, $workerIds];
    }


    /**
     * sends the events
     *
     * @param TerminateEvent $event event
     *
     * @return void
     */
    public function onKernelTerminate(TerminateEvent $event)
    {
        foreach ($this->queueToSend as $index => $sendInfo) {
            $payload = \json_encode($sendInfo[0]);
            foreach ($sendInfo[1] as $workerId) {
                $this->logger->info('Sending message to queue', ['queue' => $workerId, 'message' => $payload]);
                $this->rabbitMqProducer->send($workerId, $payload);
            }
            unset($this->queueToSend[$index]);
        }
    }

    private function getEventRefUrl(ModelEvent $event) : string
    {
        return $this->router->generate(
            sprintf('%s.put', $event->getDocumentModel()->getEntityClass(true)),
            ['id' => $event->getRecordId()],
            Router::ABSOLUTE_URL
        );
    }

    private function getDocumentEventName(ModelEvent $event) : string
    {
        $eventNames = $event->getDocumentModel()->getRuntimeDefinition()->getRestEventNames();
        $eventName = null;

        switch ($event->getEventName()) {
            case ModelEvent::MODEL_EVENT_UPDATE:
                $eventName = $eventNames['put'];
                break;
            case ModelEvent::MODEL_EVENT_INSERT:
                $eventName = $eventNames['post'];
                break;
            case ModelEvent::MODEL_EVENT_DELETE:
                $eventName = $eventNames['delete'];
                break;
        }

        return $eventName;
    }

    /**
     * Creates the structured object that will be sent to the queue (eventually..)
     *
     * @return QueueEvent event
     */
    private function createQueueEventObject(ModelEvent $event, string $eventName, string $documentUrl, string $statusUrl) : QueueEvent
    {
        $obj = new QueueEvent();
        $obj->setEvent($eventName);
        $obj->setDocumenturl($this->getWorkerRelativeUrl($documentUrl));
        $obj->setStatusurl($this->getWorkerRelativeUrl($statusUrl));
        $obj->setCoreUserId($this->securityUtils->getSecurityUsername());

        // transient headers?
        if (!is_null($event->getRequest())) {
            foreach ($this->transientHeaders as $headerName) {
                $headerVal = $event->getRequest()->headers->get($headerName);
                if (!empty($headerVal)) {
                    $obj->addTransientHeader($headerName, $headerVal);
                }
            }
        }

        return $obj;
    }

    /**
     * Creates a EventStatus object that gets persisted..
     *
     * @param QueueEvent $queueEvent queueEvent object
     *
     * @return string
     */
    private function getStatusUrl(ModelEvent $event, string $eventName, string $documentUrl, array $workerIds) : string
    {
        // we have subscribers; create the EventStatus entry
        /** @var EventStatus $eventStatus **/
        $eventStatus = new $this->eventStatusClassname();
        $eventStatus->setCreatedate(new \DateTime());
        $eventStatus->setEventname($eventName);

        // if available, transport the ref document to the eventStatus instance
        if (!empty($documentUrl)) {
            $eventStatusResource = new $this->eventStatusEventResourceClassname();
            $eventStatusResource->setRef($this->extRefConverter->getExtReference($documentUrl));
            $eventStatus->setEventresource($eventStatusResource);
        }

        foreach ($workerIds as $workerId) {
            /** @var \GravitonDyn\EventStatusBundle\Document\EventStatusStatus $eventStatusStatus **/
            $eventStatusStatus = new $this->eventStatusStatusClassname();
            $eventStatusStatus->setWorkerid($workerId);
            $eventStatusStatus->setStatus('opened');
            $eventStatus->addStatus($eventStatusStatus);
        }

        // Set username to Event
        $eventStatus->setUserid($this->securityUtils->getSecurityUsername());

        // send predispatch for other stuff happening (like restrictions)
        $event = new EntityPrePersistEvent();
        $event->setEntity($eventStatus);
        $event->setRepository(
            $this->documentManager->getRepository($this->eventStatusStatusClassname)
        );

        $this->eventDispatcher->dispatch($event, EntityPrePersistEvent::NAME);

        $this->documentManager->persist($eventStatus);
        $this->documentManager->flush();

        // get the url.
        $url = $this->router->generate(
            $this->eventStatusRouteName,
            [
                'id' => $eventStatus->getId()
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $url;
    }

    /**
     * Checks EventWorker for worker that are subscribed to our event and returns
     * their workerIds as array

     * @param QueueEvent $queueEvent queueEvent object
     *
     * @return array array of worker ids
     */
    private function getSubscribedWorkerIds(string $queueEvent) : array
    {
        // compose our regex to match stars ;-)
        // results in = /((\*|document)+)\.((\*|dude)+)\.((\*|config)+)\.((\*|update)+)/
        $routingArgs = explode('.', $queueEvent);
        $regex =
            '^'.
            implode(
                '\.',
                array_map(
                    function ($arg) {
                        return '((\*|'.$arg.')+)';
                    },
                    $routingArgs
                )
            )
            .'$';

        // look up workers by class name
        $qb = $this->documentManager->createQueryBuilder($this->eventWorkerClassname);
        $query = $qb
            ->select('id')
            ->field('subscription.event')
            ->equals(new Regex($regex))
            ->getQuery();

        $query->setHydrate(false);

        return array_map(
            function ($record) {
                return $record['_id'];
            },
            $query->execute()->toArray()
        );
    }

    /**
     * changes an uri for the workers
     *
     * @param string $uri uri
     *
     * @return string changed uri
     */
    private function getWorkerRelativeUrl(string $uri) : string
    {
        if (empty($this->workerRelativeUrl)) {
            return $uri;
        }

        $relUrl = new Uri($this->workerRelativeUrl);

        $uri = new Uri($uri);
        $uri = $uri
            ->withHost($relUrl->getHost())
            ->withScheme($relUrl->getScheme())
            ->withPort($relUrl->getPort());

        return (string) $uri;
    }
}
