<?php
/**
 * Response listener that adds an eventStatus to Link header if necessary and creates an EventStatus resource
 */

namespace Graviton\RabbitMqBundle\Listener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Graviton\RabbitMqBundle\Document\QueueEvent;
use Graviton\RestBundle\HttpFoundation\LinkHeader;
use Graviton\RestBundle\HttpFoundation\LinkHeaderItem;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

/**
 * Response listener that adds an eventStatus to Link header if necessary
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class EventStatusLinkResponseListener
{

    /**
     * @var ProducerInterface Producer for publishing messages.
     */
    private $rabbitMqProducer = null;

    /**
     * @var LoggerInterface Logger
     */
    private $logger = null;

    /**
     * @var RouterInterface Router to generate resource URLs
     */
    private $router = null;

    /**
     * @var Request request
     */
    private $request;

    /**
     * @var array mapping from class shortname ("collection") to controller service
     */
    private $documentMapping = array();

    /**
     * @var array if routinkey contains any strings in this array, we do nothing
     */
    private $bannedRoutingkeys = array();

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
     * @var string classname of the EventStatusStatus document
     */
    private $eventStatusStatusClassname;

    /**
     * @var string route name of the /event/status route
     */
    private $eventStatusRouteName;

    /**
     * @var DocumentManager Document manager
     */
    private $documentManager;

    private $actionEventMap = [
        'put' => 'update',
        'post' => 'create',
        'delete' => 'delete'
    ];

    /**
     * @param ProducerInterface $rabbitMqProducer           RabbitMQ dependency
     * @param LoggerInterface   $logger                     Logger dependency
     * @param RouterInterface   $router                     Router dependency
     * @param RequestStack      $requestStack               Request stack
     * @param DocumentManager   $documentManager            Doctrine document manager
     * @param QueueEvent        $queueEventDocument         queueevent document
     * @param array             $documentMapping            document mapping
     * @param array             $bannedRoutingkeys          banned routing keys
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
        DocumentManager $documentManager,
        QueueEvent $queueEventDocument,
        array $documentMapping,
        array $bannedRoutingkeys,
        $eventWorkerClassname,
        $eventStatusClassname,
        $eventStatusStatusClassname,
        $eventStatusRouteName
    ) {
        $this->rabbitMqProducer = $rabbitMqProducer;
        $this->logger = $logger;
        $this->router = $router;
        $this->request = $requestStack->getCurrentRequest();
        $this->documentManager = $documentManager;
        $this->queueEventDocument = $queueEventDocument;
        $this->documentMapping = $documentMapping;
        $this->eventWorkerClassname = $eventWorkerClassname;
        $this->eventStatusClassname = $eventStatusClassname;
        $this->eventStatusStatusClassname = $eventStatusStatusClassname;
        $this->eventStatusRouteName = $eventStatusRouteName;
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
        // exit if not master request or uninteresting method..
        if (!$event->isMasterRequest() || $this->isNotConcerningRequest()) {
            return;
        }

        // we can always safely call this, it doesn't need much resources.
        // only if we have subscribers, it will create more load as it persists an EventStatus
        $queueEvent = $this->createQueueEventObject();

        if (!is_null($queueEvent->getStatusurl())) {
            $linkHeader = LinkHeader::fromResponse($event->getResponse());
            $linkHeader->add(
                new LinkHeaderItem(
                    $queueEvent->getStatusurl(),
                    array('rel' => 'eventStatus')
                )
            );

            $event->getResponse()->headers->set(
                'Link',
                (string) $linkHeader
            );
        }

        // let's send it to the queue
        $this->rabbitMqProducer->publish(
            json_encode($queueEvent),
            $queueEvent->getEvent()
        );
    }

    /**
     * on GET requests, we don't want to do anything.. let's quickly find out..
     *
     * @return boolean true if it should not concern us, false otherwise
     */
    private function isNotConcerningRequest()
    {
        return in_array(
            substr($this->request->attributes->get('_route'), -4),
            ['.get', '.all']
        );
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
        $obj->setPublicurl($this->request->attributes->get('selfLink'));
        $obj->setStatusurl($this->getStatusUrl($obj));

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
        $route = $this->request->attributes->get('_route');

        list(, $bundle, , $document, $action) = explode('.', $route);

        $routingKey = 'document.'.
            $bundle.
            '.'.
            $document.
            '.'.
            $this->actionEventMap[$action];

        return $routingKey;
    }

    /**
     * Creates a EventStatus object that gets persisted..
     *
     * @param QueueEvent $queueEvent queueEvent object
     *
     * @return array array of worker ids
     */
    private function getStatusUrl($queueEvent)
    {
        // this has to be checked after cause we should not call getSubscribedWorkerIds() if above is true
        $workerIds = $this->getSubscribedWorkerIds($queueEvent);
        if (empty($workerIds)) {
            return null;
        }

        // we have subscribers; create the EventStatus entry
        $eventStatus = new $this->eventStatusClassname();
        $eventStatus->setCreatedate(new \DateTime());
        $eventStatus->setEventname($queueEvent->getEvent());

        foreach ($workerIds as $workerId) {
            $eventStatusStatus = new $this->eventStatusStatusClassname();
            $eventStatusStatus->setWorkerid($workerId);
            $eventStatusStatus->setStatus('opened');
            $eventStatus->addStatus($eventStatusStatus);
        }

        $this->documentManager->persist($eventStatus);
        $this->documentManager->flush();

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

     * @param QueueEvent $queueEvent queueEvent object
     *
     * @return array array of worker ids
     */
    private function getSubscribedWorkerIds(QueueEvent $queueEvent)
    {
        // compose our regex to match stars ;-)
        // results in = /([\*|document]+)\.([\*|dude]+)\.([\*|config]+)\.([\*|update]+)/
        $routingArgs = explode('.', $queueEvent->getEvent());
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

        $dm = $this->documentManager->createQueryBuilder($this->eventWorkerClassname);

        $data = $dm
            ->select('id')
            ->field('subscription.event')
            ->equals(new \MongoRegex($regex))
            ->getQuery()
            ->execute()->toArray();

        return array_keys($data);
    }
}
