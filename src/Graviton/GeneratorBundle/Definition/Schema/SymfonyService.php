<?php
/**
 * Part of JSON definition
 */
namespace Graviton\GeneratorBundle\Definition\Schema;

/**
 * JSON definition "service"
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SymfonyService
{

    /**
     * @var string
     */
    private $serviceName;

    /**
     * @var string
     */
    private $parent;

    /**
     * @var string
     */
    private $className;

    /**
     * @var string[]
     */
    private $arguments = [];

    /**
     * @var SymfonyServiceCall[]
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
     * @return string
     */
    public function getParent(): ?string
    {
        return $this->parent;
    }

    /**
     * @param string $parent
     */
    public function setParent(?string $parent): void
    {
        $this->parent = $parent;
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
     * @return string[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @param string[] $arguments
     */
    public function setArguments(array $arguments): void
    {
        $this->arguments = $arguments;
    }

    /**
     * get Calls
     *
     * @return SymfonyServiceCall[] Calls
     */
    public function getCalls()
    {
        return $this->calls;
    }

    /**
     * set Calls
     *
     * @param SymfonyServiceCall[] $calls calls
     *
     * @return void
     */
    public function setCalls($calls)
    {
        $this->calls = $calls;
    }
}
