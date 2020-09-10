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
class ExtAmqp implements ProducerInterface
{

    private $logger;
    private $queueHost;
    private $queuePort;
    private $queueUsername;
    private $queuePassword;
    private $queueVhost;

    public function __construct(
        $queueHost,
        $queuePort,
        $queueUsername,
        $queuePassword,
        $queueVhost
    )
    {
        $this->queueHost = $queueHost;
        $this->queuePort = $queuePort;
        $this->queueUsername = $queueUsername;
        $this->queuePassword = $queuePassword;
        $this->queueVhost = $queueVhost;
    }

    /**
     * @param mixed $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function send(string $routingKey, string $message)
    {
        $connection = new \AMQPConnection();
        $connection->setHost($this->queueHost);
        $connection->setPort($this->queuePort);
        $connection->setLogin($this->queueUsername);
        $connection->setPassword($this->queuePassword);
        $connection->setVhost($this->queueVhost);

        try {
            $connection->connect();

            $channel = new \AMQPChannel($connection);
            $exchange = new \AMQPExchange($channel);

            // try to declare worker queue
            $queue = new \AMQPQueue($channel);
            $queue->setName($routingKey);
            $queue->setFlags(AMQP_DURABLE);
            $queue->declareQueue();

            $exchange->publish($message, $routingKey, AMQP_NOPARAM, ['content_type' => 'application/json']);

            if ($this->logger instanceof LoggerInterface) {
                $this->logger->info(
                    "Sent message to AMQP queue",
                    ['routingkey' => $routingKey, 'message' => $message]
                );
            }

        } catch (\Exception $e) {
            $this->logger->error(
                "Failed sending message to AMQP queue",
                ['routingkey' => $routingKey, 'message' => $message, 'exception' => $e]
            );
        } finally {
            $connection->disconnect();
        }
    }
}
