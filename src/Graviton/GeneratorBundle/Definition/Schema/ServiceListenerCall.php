<?php
/**
 * Part of JSON definition
 */
namespace Graviton\GeneratorBundle\Definition\Schema;

/**
 * JSON definition "service" -> "listeners" -> "calls"
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ServiceListenerCall
{

    /**
     * @var string
     */
    private $method;

    /**
     * @var string[]
     */
    private $arguments;

    /**
     * get Method
     *
     * @return string Method
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * set Method
     *
     * @param string $method method
     *
     * @return void
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * get Arguments
     *
     * @return string[] Arguments
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * set Arguments
     *
     * @param string[] $arguments arguments
     *
     * @return void
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
    }
}
