<?php

namespace Graviton\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * App
 */
class App
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var boolean
     */
    private $showInMenu;

    /**
     * @var boolean
     */
    private $showInDrawer;

    /**
     * @var integer
     */
    private $id;


    /**
     * Set name
     *
     * @param string $name
     * @return App
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return App
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return App
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set showInMenu
     *
     * @param boolean $showInMenu
     * @return App
     */
    public function setShowInMenu($showInMenu)
    {
        $this->showInMenu = $showInMenu;

        return $this;
    }

    /**
     * Get showInMenu
     *
     * @return boolean 
     */
    public function getShowInMenu()
    {
        return $this->showInMenu;
    }

    /**
     * Set showInDrawer
     *
     * @param boolean $showInDrawer
     * @return App
     */
    public function setShowInDrawer($showInDrawer)
    {
        $this->showInDrawer = $showInDrawer;

        return $this;
    }

    /**
     * Get showInDrawer
     *
     * @return boolean 
     */
    public function getShowInDrawer()
    {
        return $this->showInDrawer;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}
