<?php
/**
 * Part of JSON definition
 */
namespace Graviton\GeneratorBundle\Definition\Schema;

/**
 * JSON definition "service" -> "listeners"
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ServiceListener extends SymfonyService
{

    /**
     * @var array
     */
    private $events = [];

    /**
     * get Events
     *
     * @return array Events
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * set Events
     *
     * @param array $events events
     *
     * @return void
     */
    public function setEvents($events)
    {
        $this->events = $events;
    }
}
