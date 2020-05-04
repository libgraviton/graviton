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
class ServiceListener
{

    /**
     * @var string
     */
    private $serviceName;

    /**
     * @var string
     */
    private $className;

    /**
     * @var array
     */
    private $events = [];

    /**
     * @var ServiceListenerCall[]
     */
    private $calls = [];

    /**
     * get ServiceName
     *
     * @return string ServiceName
     */
    public function getServiceName()
    {
        return $this->serviceName;
    }

    /**
     * set ServiceName
     *
     * @param string $serviceName serviceName
     *
     * @return void
     */
    public function setServiceName($serviceName)
    {
        $this->serviceName = $serviceName;
    }

    /**
     * get ClassName
     *
     * @return string ClassName
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * set ClassName
     *
     * @param string $className className
     *
     * @return void
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

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

    /**
     * get Calls
     *
     * @return ServiceListenerCall[] Calls
     */
    public function getCalls()
    {
        return $this->calls;
    }

    /**
     * set Calls
     *
     * @param ServiceListenerCall[] $calls calls
     *
     * @return void
     */
    public function setCalls($calls)
    {
        $this->calls = $calls;
    }
}
