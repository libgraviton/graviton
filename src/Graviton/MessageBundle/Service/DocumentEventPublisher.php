<?php

/**
 * Publishes document level messages to the messaging bus.
 */

namespace Graviton\MessageBundle\Service;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Graviton\MessageBundle\Document\JobStatus;
use Graviton\MessageBundle\Exception\UnknownRoutingKeyException;
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
     * @see puglishMessage()
     */
    public $additionalProperties = array();

    /**
     * @var array Holds the supported document types
     */
    public $documents = array();

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
     * @param ProducerInterface $rabbitMqProducer RabbitMQ dependency
     * @param LoggerInterface   $logger           Logger dependency
     * @param RouterInterface   $router           Router dependency
     */
    public function __construct(
        ProducerInterface $rabbitMqProducer,
        LoggerInterface $logger,
        RouterInterface $router
    ) {
        $this->rabbitMqProducer = $rabbitMqProducer;
        $this->logger = $logger;
        $this->router = $router;
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
        $this->publishEvent($args->getDocument(), 'create', $args->getDocumentManager());
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
          $this->publishEvent($args->getDocument(), 'update', $args->getDocumentManager());

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
          $this->publishEvent($args->getDocument(), 'delete', $args->getDocumentManager());

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
     * Transforms a given routeName and resource id to a resource URL.
     *
     * @param string $routeName The fully qualified route name
     * @param string $id        The resource id
     * @return string The generated URL
     */
    private function toUrl($routeName, $id)
    {
        return $this->router
            ->getGenerator()
            ->generate($routeName, ['id' => $id]);
    }


    /**
     * Creates a new JobStatus document. Then publishes it's id with a message onto the message bus.
     * The message and routing key get determined by a given document and an action name.
     *
     * @param object          $document        The document for determining message and routin key
     * @param string          $event           The action name
     * @param DocumentManager $documentManager A document manager to use for creating the JobStatus document
     * @return bool Whether a message has been successfully sent to the message bus or not
     */
    public function publishEvent($document, $event, DocumentManager $documentManager)
    {
        $documentClass = get_class($document);
        if (isset($this->documents[$documentClass])) {
            $additionalProperties = $this->additionalProperties;
            $additionalProperties['correlation_id'] = $this->createJobStatus($documentManager)->getId();
            try {
                $this->rabbitMqProducer->publish(
                    $this->toUrl($this->documents[$documentClass], $document->getId()),
                    $this->toRoutingKey($this->documents[$documentClass], $event),
                    $additionalProperties
                );
                return true;
            } catch (UnknownRoutingKeyException $e) {
                $this->logger->warn($e->getMessage());
                return false;
            }
        }
        return false;
    }

    /**
     * Creates a JobStatus Document
     *
     * @param DocumentManager $documentManager A document manager to use for creating the JobStatus document
     * @return object The created Document
     */
    private function createJobStatus(DocumentManager $documentManager)
    {
        $document = new JobStatus();
        $documentManager->persist($document);
        $documentManager->flush();
        return $documentManager->find(get_class($document), $document->getId());
    }
}
