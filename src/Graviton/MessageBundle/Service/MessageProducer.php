<?php

/**
 * Publishes messages to the messaging bus. This producer only allows specifically white-listed messages (config).
 */

namespace Graviton\MessageBundle\Service;

use Graviton\MessageBundle\Exception\UnknownRoutingKeyException;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

/**
 * Publishes messages to the messaging bus. This producer only allows specifically white-listed messages (config).
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class MessageProducer extends Producer
{

    /**
     * @var string Defines the default reply_to property
     */
    public $replyTo = 'graviton.message.status';

    /**
     * @var array Holds all registered / allowed routing keys
     */
    public $registeredRoutingKeys = array();

    /**
     * @override
     *
     * Publishes a given message to the messaging bus.
     * If not already set, reply_to will be set to the default value.
     *
     * @see replyTo
     *
     * @param string $msgBody The message
     * @param string $routingKey The routing key
     * @param array $additionalProperties All additional properties
     * @throws UnknownRoutingKeyException When the given routing key is not registered
     */
    public function publish($msgBody, $routingKey = '', $additionalProperties = array())
    {
        $additionalProperties['reply_to'] = isset($additionalProperties['reply_to']) ?
            $additionalProperties['reply_to'] : $this->replyTo;
        $this->validateRoutingKey($routingKey);
       parent::publish($msgBody, $routingKey, $additionalProperties);
    }

    /**
     * Validates whether the given routing key is registered or not.
     *
     * @param $routingKey The routing key
     * @throws UnknownRoutingKeyException When the given routing key is not registered.
     */
    public function validateRoutingKey($routingKey)
    {
        if (!in_array($routingKey, $this->registeredRoutingKeys)) {
            throw new UnknownRoutingKeyException($routingKey);
        }
    }

}