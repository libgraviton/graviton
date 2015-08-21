<?php

namespace Graviton\MessageBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class TestConsumer implements ConsumerInterface
{
    public function execute(AMQPMessage $msg)
    {
        echo 'MSG: ' . $msg->body . "\n";
    }
}