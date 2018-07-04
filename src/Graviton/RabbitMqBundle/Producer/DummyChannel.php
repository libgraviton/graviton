<?php
/**
 * dummy job producer
 */

namespace Graviton\RabbitMqBundle\Producer;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
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
     * @param string $queue tmp value
     * @param bool   $fooA  tmp value
     * @param bool   $fooB  tmp value
     * @param bool   $fooC  tmp value
     * @param bool   $fooD  tmp value
     * @return void
     */
    public function queueDeclare($queue, $fooA, $fooB, $fooC, $fooD)
    {
    }
}
