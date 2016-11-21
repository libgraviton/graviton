<?php
/**
 * dummy job producer
 */

namespace Graviton\RabbitMqBundle\Producer;

use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class Dummy implements ProducerInterface
{
    /** @var array list of events */
    private $eventList = [];

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
        $this->eventList[] = $msgBody;
    }

    /**
     * Dummy.
     *
     * @return DummyChannel
     */
    public function getChannel()
    {
        return new DummyChannel();
    }

    /**
     * Reset event list
     * @return void
     */
    public function resetEventList()
    {
        $this->eventList = [];
    }

    /**
     * Get current event list
     *
     * @return array
     */
    public function getEventList()
    {
        return $this->eventList;
    }

    /**
     * Dummy setter
     * @param string $d dummy value
     * @return void
     */
    public function setContentType($d)
    {
    }

    /**
     * Dummy setter
     * @param string $d dummy value
     * @return void
     */
    public function setExchangeOptions($d)
    {
    }

    /**
     * Dummy setter
     * @param string $d dummy value
     * @return void
     */
    public function setQueueOptions($d)
    {
    }

    /**
     * Dummy setter
     * @param bool $d dummy value
     * @return void
     */
    public function disableAutoSetupFabric($d = false)
    {
    }

    /**
     * Dummy setter
     * @param bool $d dummy value
     * @return void
     */
    public function setEventDispatcher($d = false)
    {
    }
}
