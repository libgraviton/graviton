<?php

/**
 * Consumes RabbitMQ messages and dumps them.
 */

namespace Graviton\MessageBundle\Service;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Consumes RabbitMQ messages and dumps them.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DumpConsumer implements ConsumerInterface
{

    /**
     * Callback executed when a message is received. Dumps the message body, delivery_info and properties.
     *
     * @param AMQPMessage $msg The rceived message.
     *
     * @return void
     */
    public function execute(AMQPMessage $msg)
    {
        echo 'Message received at ' . date('c') . ': ' . $msg->body . "\n";
        echo "\t" . 'Delivery Info: ' . "\n";
        foreach ($msg->delivery_info as $key => $value) {
            if (is_scalar($value)) {
                echo "\t\t: " . $key . ' ' . $value . "\n";
            }
        }
        echo "\t" . 'Properties: ' . "\n";
        foreach ($msg->get_properties() as $property => $value) {
            if (is_scalar($value)) {
                echo "\t\t: " . $property . ' ' . $value . "\n";
            }
        }
    }
}
