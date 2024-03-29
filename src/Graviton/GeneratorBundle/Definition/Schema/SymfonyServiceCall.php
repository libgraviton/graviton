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
class SymfonyServiceCall
{

    /**
     * @var string
     */
    private $method;

    /**
     * @var mixed[]
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
     * @return mixed[] Arguments
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * set Arguments
     *
     * @param mixed[] $arguments arguments
     *
     * @return void
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
    }
}
