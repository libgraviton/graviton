<?php

/**
 * Publishes document level messages to the messaging bus.
 */

namespace Graviton\MessageBundle\Service;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Graviton\MessageBundle\Exception\UnknownRoutingKeyException;
use Monolog\Logger;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

/**
 * Publishes document level messages to the messaging bus.
 *
 * C@author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DoctrineEventPublisher implements EventSubscriber
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
     * @var null|ProducerInterface
     */
    protected $rabbitMqProducer = null;

    /**
     * @var Logger|null
     */
    protected $logger = null;

    /**
     * @var null
     */
    protected $router = null;

    /**
     * @param ProducerInterface $rabbitMqProducer
     * @param Logger $logger
     * @param $router
     */
    public function __construct(
        ProducerInterface $rabbitMqProducer,
        Logger $logger,
        $router
    ) {
        $this->rabbitMqProducer = $rabbitMqProducer;
        $this->logger = $logger;
        $this->router = $router;
    }

    /**
     * @return array
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
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->publishMessage($args->getDocument(), 'create');
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
          $this->publishMessage($args->getDocument(), 'update');

    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
          $this->publishMessage($args->getDocument(), 'delete');

    }

    /**
     * @param $route
     * @param string $suffix
     * @return mixed|string
     */
    protected function toRoutingKey($route, $suffix = '')
    {
        $prefix = $this->router->getRouteCollection()->get($route)->compile()->getStaticPrefix();
        $baseKey = str_replace('/', '.', trim($prefix, '/'));
        return strlen($suffix) ? $baseKey . '.' . $suffix : $baseKey;
    }

    /**
     * @param $route
     * @param $id
     * @return mixed
     */
    protected function toRoute($route, $id)
    {
        return $this->router
            ->getGenerator()
            ->generate($route, ['id' => $id]);
    }

    /**
     * @param $document
     * @param $event
     * @return bool
     */
    private function publishMessage($document, $event)
    {
        $documentClass = get_class($document);

        if (isset($this->documents[$documentClass])) {
            try {
                $this->rabbitMqProducer->publish(
                    $this->toRoute($this->documents[$documentClass], $document->getId()),
                    $this->toRoutingKey($this->documents[$documentClass], $event),
                    $this->additionalProperties
                );
                return true;
            } catch (UnknownRoutingKeyException $e) {
                $this->logger->warn($e->getMessage());
                return false;
            }
        }
        return false;
    }

    private function createJobStatus($documentManager)
    {
        $document = new JobStatus();
    }
}
