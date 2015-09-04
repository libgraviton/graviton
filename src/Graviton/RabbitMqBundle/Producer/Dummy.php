<?php
/**
 * dummy publisher
 */

namespace Graviton\RabbitMqBundle\Producer;

use OldSound\RabbitMqBundle\RabbitMq\Producer;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class Dummy extends Producer
{

    /**
     * Publishes the message and merges additional properties with basic properties
     *
     * @param string $msgBody              body
     * @param string $routingKey           routingKey
     * @param array  $additionalProperties properties
     *
     * @return void
     */
    public function publish($msgBody, $routingKey = '', $additionalProperties = array())
    {
    }
}
