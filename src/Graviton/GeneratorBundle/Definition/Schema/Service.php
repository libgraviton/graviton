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
class Service
{
    /**
     * @var bool
     */
    private $readOnly;
    /**
     * @var bool
     */
    private bool $useSecondary = false;
    /**
     * @var bool
     */
    private $versioning;
    /**
     * @var bool
     */
    private $recordOriginModifiable;
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
     * @var SymfonyServiceCall[]
     */
    private $baseControllerCalls = [];
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
     * @var string
     */
    private $collectionName;
    /**
     * @var array
     */
    private $variations = [];
    /**
     * @var array
     */
    private $listeners = [];
    /**
     * @var array
     */
    private $services = [];

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
     * get UseSecondary
     *
     * @return bool UseSecondary
     */
    public function isUseSecondary()
    {
        return $this->useSecondary;
    }

    /**
     * set UseSecondary
     *
     * @param bool $useSecondary useSecondary
     *
     * @return void
     */
    public function setUseSecondary($useSecondary)
    {
        $this->useSecondary = $useSecondary;
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
     * @return array calls
     */
    public function getBaseControllerCalls(): array
    {
        return $this->baseControllerCalls;
    }

    /**
     * @param SymfonyServiceCall[] $baseControllerCalls calls
     *
     * @return void
     */
    public function setBaseControllerCalls(array $baseControllerCalls): void
    {
        $this->baseControllerCalls = $baseControllerCalls;
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
     * @return bool
     */
    public function getVersioning()
    {
        return is_null($this->versioning) ? false : $this->versioning;
    }

    /**
     * @param bool $versioning Is a versioned service
     * @return $this
     */
    public function setVersioning($versioning)
    {
        $this->versioning = $versioning;
        return $this;
    }

    /**
     * @return bool
     */
    public function getRecordOriginModifiable()
    {
        return $this->recordOriginModifiable;
    }

    /**
     * @param bool $recordOriginModifiable Is origin record modifiable
     * @return $this
     */
    public function setRecordOriginModifiable($recordOriginModifiable)
    {
        $this->recordOriginModifiable = $recordOriginModifiable;

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

    /**
     * @return string
     */
    public function getCollectionName()
    {
        return $this->collectionName;
    }

    /**
     * @param string $collectionName name of colleciton
     * @return $this
     */
    public function setCollectionName($collectionName)
    {
        $this->collectionName = $collectionName;
        return $this;
    }

    /**
     * get Variations
     *
     * @return array Variations
     */
    public function getVariations()
    {
        return $this->variations;
    }

    /**
     * set Variations
     *
     * @param array $variations variations
     *
     * @return void
     */
    public function setVariations($variations)
    {
        $this->variations = $variations;
    }

    /**
     * get Listeners
     *
     * @return array Listeners
     */
    public function getListeners()
    {
        return $this->listeners;
    }

    /**
     * set Listeners
     *
     * @param array $listeners listeners
     *
     * @return void
     */
    public function setListeners($listeners)
    {
        $this->listeners = $listeners;
    }

    /**
     * @return array
     */
    public function getServices(): array
    {
        return $this->services;
    }

    /**
     * @param array $services
     */
    public function setServices(array $services): void
    {
        $this->services = $services;
    }
}
