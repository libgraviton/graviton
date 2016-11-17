<?php
/**
 * dummy job producer
 */

namespace Graviton\RabbitMqBundle\Producer;

use OldSound\RabbitMqBundle\RabbitMq\Producer;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DummyChannel
{
    /**
     * Faking the not conventional call queue_declare
     *
     * @param string $method    Simple implementation to match our camelcase validation
     * @param array  $arguments Data list
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        $method = 'queueDeclare';
        if (isset($this->$method)) {
            call_user_func_array($this->$method, array_merge([&$this], $arguments));
        }
    }

    /**
     * Dummy function queue_declare
     * @param string $queue  tmp value
     * @param bool   $false1 tmp value
     * @param bool   $true1  tmp value
     * @param bool   $false2 tmp value
     * @param bool   $false3 tmp value
     * @return void
     */
    public function queueDeclare($queue, $false1, $true1, $false2, $false3)
    {
    }
}
