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
use Graviton\RabbitMqBundle\Document\QueueEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Graviton\SecurityBundle\Service\SecurityUtils;
use GravitonDyn\EventStatusBundle\Document\EventStatus;
use Zend\Diactoros\Uri;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class EventStatusLinkResponseListener
{

    /**
     * @var ProducerInterface Producer for publishing messages.
     */
    private $rabbitMqProducer = null;

    /**
     * @var RouterInterface Router to generate resource URLs
     */
    private $router = null;

    /**
     * @var Request request
     */
    private $request;

    /**
     * @var QueueEvent queue event document
     */
    private $queueEventDocument;

    /**
     * @var array
     */
    private $eventMap;

    /**
     * @var ExtReferenceConverter ExtReferenceConverter
     */
    private $extRefConverter;

    /**
     * @var string classname of the EventWorker document
     */
    private $eventWorkerClassname;

    /**
     * @var string classname of the EventStatus document
     */
    private $eventStatusClassname;

    /**
     * @var string classname of the EventStatusStatus document
     */
    private $eventStatusStatusClassname;

    /**
     * @var string classname of the EventStatusEventResource document
     */
    private $eventStatusEventResourceClassname;

    /**
     * @var string route name of the /event/status route
     */
    private $eventStatusRouteName;

    /**
     * @var DocumentManager Document manager
     */
    private $documentManager;

    /**
     * @var SecurityUtils
     */
    protected $securityUtils;

    /**
     * @var Uri
     */
    protected $workerRelativeUrl;

    /**
     * @param ProducerInterface     $rabbitMqProducer                  RabbitMQ dependency
     * @param RouterInterface       $router                            Router dependency
     * @param RequestStack          $requestStack                      Request stack
     * @param DocumentManager       $documentManager                   Doctrine document manager
     * @param ExtReferenceConverter $extRefConverter                   instance of the ExtReferenceConverter service
     * @param QueueEvent            $queueEventDocument                queueevent document
     * @param array                 $eventMap                          eventmap
     * @param string                $eventWorkerClassname              classname of the EventWorker document
     * @param string                $eventStatusClassname              classname of the EventStatus document
     * @param string                $eventStatusStatusClassname        classname of the EventStatusStatus document
     * @param string                $eventStatusEventResourceClassname classname of the E*S*E*Resource document
     * @param string                $eventStatusRouteName              name of the route to EventStatus
     * @param SecurityUtils         $securityUtils                     Security utils service
     * @param string                $workerRelativeUrl                 backend url relative from the workers
     */
    public function __construct(
        ProducerInterface $rabbitMqProducer,
        RouterInterface $router,
        RequestStack $requestStack,
        DocumentManager $documentManager,
        ExtReferenceConverter $extRefConverter,
        QueueEvent $queueEventDocument,
        array $eventMap,
        $eventWorkerClassname,
        $eventStatusClassname,
        $eventStatusStatusClassname,
        $eventStatusEventResourceClassname,
        $eventStatusRouteName,
        SecurityUtils $securityUtils,
        $workerRelativeUrl
    ) {
        $this->rabbitMqProducer = $rabbitMqProducer;
        $this->router = $router;
        $this->request = $requestStack->getCurrentRequest();
        $this->documentManager = $documentManager;
        $this->extRefConverter = $extRefConverter;
        $this->queueEventDocument = $queueEventDocument;
        $this->eventMap = $eventMap;
        $this->eventWorkerClassname = $eventWorkerClassname;
        $this->eventStatusClassname = $eventStatusClassname;
        $this->eventStatusStatusClassname = $eventStatusStatusClassname;
        $this->eventStatusEventResourceClassname = $eventStatusEventResourceClassname;
        $this->eventStatusRouteName = $eventStatusRouteName;
        $this->securityUtils = $securityUtils;
        if (!is_null($workerRelativeUrl)) {
            $this->workerRelativeUrl = new Uri($workerRelativeUrl);
        }
    }

    /**
     * add a rel=eventStatus Link header to the response if necessary
     *
     * @param FilterResponseEvent $event response listener event
     *
     * @return void
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        /**
         * @var Response $response
         */
        $response = $event->getResponse();

        // exit if not master request, uninteresting method or an error occurred
        if (!$event->isMasterRequest() || $this->isNotConcerningRequest() || !$response->isSuccessful()) {
            return;
        }

        // we can always safely call this, it doesn't need much resources.
        // only if we have subscribers, it will create more load as it persists an EventStatus
        $queueEvent = $this->createQueueEventObject();

        if (!empty($queueEvent->getStatusurl()) && !empty($queueEvent->getEvent())) {
            $linkHeader = LinkHeader::fromString($response->headers->get('Link', null));
            $linkHeader->add(
                new LinkHeaderItem(
                    $queueEvent->getStatusurl(),
                    'eventStatus'
                )
            );

            $response->headers->set(
                'Link',
                (string) $linkHeader
            );
        }

        // let's send it to the queue(s) if appropriate
        if (!empty($queueEvent->getEvent())) {
            $queuesForEvent = $this->getSubscribedWorkerIds($queueEvent);

            // if needed and activated, change urls relative to workers
            if (!empty($queuesForEvent) && $this->workerRelativeUrl instanceof Uri) {
                $queueEvent = $this->getWorkerQueueEvent($queueEvent);
            }

            foreach ($queuesForEvent as $queueForEvent) {
                // declare the Queue for the Event if its not there already declared
                $this->rabbitMqProducer->getChannel()->queue_declare($queueForEvent, false, true, false, false);
                $this->rabbitMqProducer->publish(json_encode($queueEvent), $queueForEvent);
            }
        }
    }

    /**
     * we only want to do something if we have a mapped event..
     *
     * @return boolean true if it should not concern us, false otherwise
     */
    private function isNotConcerningRequest()
    {
        return is_null($this->generateRoutingKey());
    }

    /**
     * Creates the structured object that will be sent to the queue (eventually..)
     *
     * @return QueueEvent event
     */
    private function createQueueEventObject()
    {
        $obj = clone $this->queueEventDocument;
        $obj->setEvent($this->generateRoutingKey());
        $obj->setDocumenturl($this->request->get('selfLink'));
        $obj->setStatusurl($this->getStatusUrl($obj));
        $obj->setCoreUserId($this->getSecurityUsername());

        return $obj;
    }

    /**
     * compose our routingKey. this will have the form of 'document.[bundle].[document].[event]'
     * rules:
     *  * always 4 parts divided by points.
     *  * in this context (doctrine/odm stuff) we prefix with 'document.'
     *
     * @return string routing key
     */
    private function generateRoutingKey()
    {
        $routeParts = explode('.', $this->request->get('_route'));
        $action = array_pop($routeParts);
        $baseRoute = implode('.', $routeParts);

        // find our route in the map
        $routingKey = null;

        foreach ($this->eventMap as $mapElement) {
            if ($mapElement['baseRoute'] == $baseRoute &&
                isset($mapElement['events'][$action])
            ) {
                $routingKey = $mapElement['events'][$action];
                break;
            }
        }

        return $routingKey;
    }

    /**
     * Creates a EventStatus object that gets persisted..
     *
     * @param QueueEvent $queueEvent queueEvent object
     *
     * @return string
     */
    private function getStatusUrl($queueEvent)
    {
        // this has to be checked after cause we should not call getSubscribedWorkerIds() if above is true
        $workerIds = $this->getSubscribedWorkerIds($queueEvent);
        if (empty($workerIds)) {
            return '';
        }

        // we have subscribers; create the EventStatus entry
        /** @var EventStatus $eventStatus **/
        $eventStatus = new $this->eventStatusClassname();
        $eventStatus->setCreatedate(new \DateTime());
        $eventStatus->setEventname($queueEvent->getEvent());

        // if available, transport the ref document to the eventStatus instance
        if (!empty($queueEvent->getDocumenturl())) {
            $eventStatusResource = new $this->eventStatusEventResourceClassname();
            $eventStatusResource->setRef($this->extRefConverter->getExtReference($queueEvent->getDocumenturl()));
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
        $eventStatus->setUserid($this->getSecurityUsername());

        $this->documentManager->persist($eventStatus);
        $this->documentManager->flush();

        // get the url..
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
    private function getSubscribedWorkerIds(QueueEvent $queueEvent)
    {
        // compose our regex to match stars ;-)
        // results in = /((\*|document)+)\.((\*|dude)+)\.((\*|config)+)\.((\*|update)+)/
        $routingArgs = explode('.', $queueEvent->getEvent());
        $regex =
            '/'.
            implode(
                '\.',
                array_map(
                    function ($arg) {
                        return '((\*|'.$arg.')+)';
                    },
                    $routingArgs
                )
            ).
            '/';

        // look up workers by class name
        $qb = $this->documentManager->createQueryBuilder($this->eventWorkerClassname);
        $data = $qb
            ->select('id')
            ->field('subscription.event')
            ->equals(new \MongoRegex($regex))
            ->getQuery()
            ->execute()
            ->toArray();

        return array_keys($data);
    }

    /**
     * Security needs to be enabled to get
     *
     * @return String
     */
    private function getSecurityUsername()
    {
        if ($this->securityUtils->isSecurityUser()) {
            return $this->securityUtils->getSecurityUsername();
        }

        return '';
    }

    /**
     * Changes the urls in the QueueEvent for the workers
     *
     * @param QueueEvent $queueEvent queue event
     *
     * @return QueueEvent altered queue event
     */
    private function getWorkerQueueEvent(QueueEvent $queueEvent)
    {
        $queueEvent->setDocumenturl($this->getWorkerRelativeUrl($queueEvent->getDocumenturl()));
        $queueEvent->setStatusurl($this->getWorkerRelativeUrl($queueEvent->getStatusurl()));
        return $queueEvent;
    }

    /**
     * changes an uri for the workers
     *
     * @param string $uri uri
     *
     * @return string changed uri
     */
    private function getWorkerRelativeUrl($uri)
    {
        $uri = new Uri($uri);
        $uri = $uri
            ->withHost($this->workerRelativeUrl->getHost())
            ->withScheme($this->workerRelativeUrl->getScheme())
            ->withPort($this->workerRelativeUrl->getPort());
        return (string) $uri;
    }
}
