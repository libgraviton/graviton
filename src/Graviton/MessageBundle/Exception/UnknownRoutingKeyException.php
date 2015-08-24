<?php

namespace Graviton\MessageBundle\Exception;

use Exception;

class UnknownRoutingKeyException extends Exception
{

    public function __construct($routingKey)
    {
        $this->message =
            'Tried to send a message with routing key "' . $routingKey . '", which is unknown.'
            . ' Add it to the list of allowed routing keys to publish such messages.';
    }

}