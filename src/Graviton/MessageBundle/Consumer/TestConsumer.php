<?php

namespace Graviton\MessageBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class TestConsumer implements ConsumerInterface
{
    public function execute(AMQPMessage $msg)
    {
        echo 'MSG: ' . $msg->body . "\n";
        echo 'correlation_id: ' . $msg->get('correlation_id') . "\n";
        echo 'message_id: ' . $msg->get('message_id') . "\n";
        echo 'reply_to: ' . $msg->get('reply_to') . "\n";
    }
}
