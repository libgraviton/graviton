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

    public function send(string $routingKey, string $message);
    public function setLogger(LoggerInterface $logger): void;

}
