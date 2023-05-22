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
    private LoggerInterface $logger;

    /**
     * set logger instance
     *
     * @param LoggerInterface $logger logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * send a message
     *
     * @param string $routingKey routing key
     * @param string $message    message
     *
     * @return void
     */
    public function send(string $routingKey, string $message): void
    {
        $this->eventList[] = $message;
    }

    /**
     * get event list
     *
     * @return array event list
     */
    public function getEventList(): array
    {
        return $this->eventList;
    }

    /**
     * clears event list
     *
     * @return void
     */
    public function resetEventList(): void
    {
        $this->eventList = [];
    }
}
