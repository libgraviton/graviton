<?php
/**
 * Part of JSON definition
 */
namespace Graviton\GeneratorBundle\Definition\Schema;

/**
 * JSON definition "service"
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class Service
{
    /**
     * @var bool
     */
    private $readOnly;
    /**
     * @var string
     */
    private $routerBase;
    /**
     * @var string
     */
    private $parent;
    /**
     * @var string
     */
    private $baseController;
    /**
     * @var string[]
     */
    private $roles = [];
    /**
     * @var int
     */
    private $fixtureOrder;
    /**
     * @var array[]
     */
    private $fixtures = [];

    /**
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param string $parent Parent service ID
     * @return $this
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return string
     */
    public function getBaseController()
    {
        return $this->baseController;
    }

    /**
     * @param string $baseController Base controller class
     * @return $this
     */
    public function setBaseController($baseController)
    {
        $this->baseController = $baseController;
        return $this;
    }

    /**
     * @return bool
     */
    public function getReadOnly()
    {
        return $this->readOnly;
    }

    /**
     * @param bool $readOnly Is readOnly service
     * @return $this
     */
    public function setReadOnly($readOnly)
    {
        $this->readOnly = $readOnly;
        return $this;
    }

    /**
     * @return string
     */
    public function getRouterBase()
    {
        return $this->routerBase;
    }

    /**
     * @param string $routerBase Base URL
     * @return $this
     */
    public function setRouterBase($routerBase)
    {
        $this->routerBase = $routerBase;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param string[] $roles Service roles
     * @return $this
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @return int
     */
    public function getFixtureOrder()
    {
        return $this->fixtureOrder;
    }

    /**
     * @param int $fixtureOrder Fixture order
     * @return $this
     */
    public function setFixtureOrder($fixtureOrder)
    {
        $this->fixtureOrder = $fixtureOrder;
        return $this;
    }

    /**
     * @return array[]
     */
    public function getFixtures()
    {
        return $this->fixtures;
    }

    /**
     * @param array[] $fixtures Fixtures
     * @return $this
     */
    public function setFixtures(array $fixtures)
    {
        $this->fixtures = $fixtures;
        return $this;
    }
}
