<?php
/**
 * Document for representing Apps.
 */

namespace Graviton\CoreBundle\Document;

use Graviton\I18nBundle\Document\TranslatableDocumentInterface;

/**
 * App
 *
 * @category GravitonCoreBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @author   Dario Nuevo <Dario.Nuevo@swisscom.com>
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/MIT MIT License (c) 2015 Swisscom
 * @link     http://swisscom.ch
 */
class App implements TranslatableDocumentInterface
{
    /**
     * @var string app id
     */
    protected $id;

    /**
     * @var string app title
     */
    protected $title;

    /**
     * @var boolean show app in menu
     */
    protected $showInMenu = false;

    /**
     * make title translatable
     *
     * @return string[]
     */
    public function getTranslatableFields()
    {
        return array('title');
    }

    /**
     * Set id
     *
     * @param string $id id for new document
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title title used for display
     *
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set showInMenu
     *
     * @param boolean $showInMenu show app in menu
     *
     * @return self
     */
    public function setShowInMenu($showInMenu)
    {
        $this->showInMenu = $showInMenu;

        return $this;
    }

    /**
     * Get showInMenu
     *
     * @return boolean $showInMenu
     */
    public function getShowInMenu()
    {
        return $this->showInMenu;
    }
}
