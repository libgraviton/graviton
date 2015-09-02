<?php

/**
 * Publishes document level messages to the messaging bus.
 */

namespace Graviton\RabbitMqBundle\Service;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Graviton\RabbitMqBundle\Document\QueueEvent;
use Graviton\RabbitMqBundle\Exception\UnknownRoutingKeyException;
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
class DocumentEventPublisher implements EventSubscriber
{

    /**
     * @var array Holds additionalProperties to be sent with the message.
     * @see publishMessage()
     */
    public $additionalProperties = array();

    /**
     * @var ProducerInterface Producer for publishing messages.
     */
    protected $rabbitMqProducer = null;

    /**
     * @var Logger Logger
     */
    protected $logger = null;

    /**
     * @var RouterInterface Router to generate resource URLs
     */
    protected $router = null;

    /**
     * @var array mapping from class shortname ("collection") to controller service
     */
    private $documentMapping = array();

    /**
     * @var QueueEvent queueevent document
     */
    private $queueEventDocument;

    /**
     * @param ProducerInterface $rabbitMqProducer   RabbitMQ dependency
     * @param LoggerInterface   $logger             Logger dependency
     * @param RouterInterface   $router             Router dependency
     * @param QueueEvent        $queueEventDocument queueevent document
     * @param array             $documentMapping    document mapping
     */
    public function __construct(
        ProducerInterface $rabbitMqProducer,
        LoggerInterface $logger,
        RouterInterface $router,
        QueueEvent $queueEventDocument,
        $documentMapping
    ) {
        $this->rabbitMqProducer = $rabbitMqProducer;
        $this->logger = $logger;
        $this->router = $router;
        $this->queueEventDocument = $queueEventDocument;
        $this->documentMapping = $documentMapping;
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
        $this->publishEvent($args->getDocument(), 'create');
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
        $this->publishEvent($args->getDocument(), 'update');
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
        $this->publishEvent($args->getDocument(), 'delete');
    }

    /**
     * Transforms a routeName into a rabbitmq topic based routing key.
     *
     * @param string $routeName The routeName
     * @param string $suffix    Suffix to append to the routing key
     * @return string The routing key
     */
    private function toRoutingKey($routeName, $suffix = '')
    {
        $prefix = $this->router->getRouteCollection()->get($routeName)->compile()->getStaticPrefix();
        $baseKey = str_replace('/', '.', trim($prefix, '/'));
        return strlen($suffix) ? $baseKey . '.' . $suffix : $baseKey;
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
            $obj->setPublicurl($this->router->generate(
                $this->documentMapping[$shortName].'.get',
                ['id' => $document->getId()],
                true
            ));
        }

        return $obj;
    }

    /**
     * Creates a new JobStatus document. Then publishes it's id with a message onto the message bus.
     * The message and routing key get determined by a given document and an action name.
     *
     * @param object $document The document for determining message and routing key
     * @param string $event    The action name
     *
     * @return bool Whether a message has been successfully sent to the message bus or not
     */
    public function publishEvent($document, $event)
    {
        $queueObject = $this->createQueueEventObject($document, $event);

        try {
            $this->rabbitMqProducer->publish(
                json_encode($queueObject),
                'core.app.dude'
            );
        } catch (UnknownRoutingKeyException $e) {
            $this->logger->warn($e->getMessage());
            // @todo: set job status to failed
            return false;
        }
        return true;
    }

    /**
     * Creates a JobStatus Document
     *
     * @param DocumentManager $documentManager A document manager to use for creating the JobStatus document
     * @return object The created Document
     */
    private function createJobStatus(DocumentManager $documentManager)
    {
        /*
        $document = new JobStatus();
        $documentManager->persist($document);
        $documentManager->flush();
        return $documentManager->find(get_class($document), $document->getId());
        */
    }
}
