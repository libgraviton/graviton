<?php
/**
 * interface for producer
 */

namespace Graviton\RabbitMqBundle\Producer;

use Psr\Log\LoggerInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
interface ProducerInterface
{

    /**
     * send a message
     *
     * @param string $routingKey routing key
     * @param string $message    message
     *
     * @return void
     */
    public function send(string $routingKey, string $message): void;

    /**
     * set logger instance
     *
     * @param LoggerInterface $logger logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger): void;
}
