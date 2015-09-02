<?php

/**
 * Publishes messages to the messaging bus. This producer only allows specifically white-listed messages (config).
 */

namespace Graviton\RabbitMqBundle\Service;

use Graviton\RabbitMqBundle\Exception\UnknownRoutingKeyException;
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
     * Publishes a given message to the messaging bus.
     * If not already set, reply_to will be set to the default value.
     *
     * @see replyTo
     *
     * @param string $msgBody              The message to be sent to the queueing server.
     * @param string $routingKey           The worker channel identifier to be used (e.g. core.app.create)
     * @param array  $additionalProperties All additional properties
     * @throws UnknownRoutingKeyException When the given routing key is not registered
     *
     * @return void
     */
    public function publish($msgBody, $routingKey = '', $additionalProperties = array())
    {
        $additionalProperties['reply_to'] = isset($additionalProperties['reply_to']) ?
            $additionalProperties['reply_to'] : $this->replyTo;
        //$this->validateRoutingKey($routingKey);
        parent::publish($msgBody, $routingKey, $additionalProperties);
    }

    /**
     * Validates whether the given routing key is registered or not.
     *
     * @param string $routingKey The routing key
     * @throws UnknownRoutingKeyException When the given routing key is not registered.
     *
     * @return void
     */
    protected function validateRoutingKey($routingKey)
    {
        if (!in_array($routingKey, $this->registeredRoutingKeys)) {
            throw new UnknownRoutingKeyException($routingKey);
        }
    }
}
