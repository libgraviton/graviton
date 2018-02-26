<?php

/**
 * Consumes RabbitMQ messages and dumps them.
 */

namespace Graviton\RabbitMqBundle\Service;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Consumes RabbitMQ messages and dumps them.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class DumpConsumer implements ConsumerInterface
{

    /**
     * Callback executed when a message is received. Dumps the message body, delivery_info and properties.
     *
     * @param AMQPMessage $msg The received message.
     *
     * @return void
     */
    public function execute(AMQPMessage $msg)
    {
        echo str_repeat('-', 60).PHP_EOL;

        echo '[RECV ' . date('c') . ']' . PHP_EOL .'<content>' .
            PHP_EOL . $msg->body . PHP_EOL . '</content>'.PHP_EOL.PHP_EOL;

        echo "*" . ' INFO ' . PHP_EOL;
        foreach ($msg->{'delivery_info'} as $key => $value) {
            if (is_scalar($value)) {
                echo "** " . $key . ' = ' . $value . PHP_EOL;
            }
        }
        echo "*" . ' PROPERTIES ' . PHP_EOL;
        foreach ($msg->get_properties() as $property => $value) {
            if (is_scalar($value)) {
                echo "** " . $property . ' = ' . $value . PHP_EOL;
            }
        }
    }
}
