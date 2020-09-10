<?php
/**
 * producer for ext-amqp
 */

namespace Graviton\RabbitMqBundle\Producer;

use Psr\Log\LoggerInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class Dummy implements ProducerInterface
{

    private array $eventList = [];

    /**
     * @param mixed $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function send(string $routingKey, string $message)
    {
        $this->eventList[] = $message;
    }

    /**
     * @return array
     */
    public function getEventList(): array
    {
        return $this->eventList;
    }

    public function resetEventList(): void
    {
        $this->eventList = [];
    }
}
