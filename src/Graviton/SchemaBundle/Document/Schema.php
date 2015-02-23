<?php

namespace Graviton\SchemaBundle\Document;

use Graviton\I18nBundle\Document\TranslatableDocumentInterface;

/**
 * Graviton\SchemaBundle\Document\Schema
 *
 * @category GravitonSchemaBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class Schema implements TranslatableDocumentInterface
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var Schema
     */
    protected $items;

    /**
     * @var Schema[]
     */
    protected $properties = array();

    /**
     * @var string[]
     */
    protected $required = array();

    /**
     * @var boolean
     */
    protected $translatable;

    /**
     * {@inheritDoc}
     *
     * @return string[]
     */
    public function getTranslatableFields()
    {
        return array();
    }

    /**
     * set title
     *
     * @param string $title title
     *
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * set description
     *
     * @param string $description description
     *
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * set type
     *
     * @param string $type type
     *
     * @return void
     */
    public function setType($type)
    {
        if ($type === 'int') {
            $type = 'integer';
        }
        if ($type === 'hash') {
            $type = 'object';
        }
        $this->type = $type;
    }

    /**
     * get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * set items
     *
     * @param Schema $items items schema
     *
     * @return void
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * get items
     *
     * @return Schema
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * add a property
     *
     * @param string $name     property name
     * @param Schema $property property
     *
     * @return void
     */
    public function addProperty($name, $property)
    {
        $this->properties[$name] = $property;
    }

    /**
     * removes a property
     *
     * @param string $name     property name
     *
     * @return void
     */
    public function removeProperty($name)
    {
        unset($this->properties[$name]);
    }

    /**
     * returns a property
     *
     * @param string $name     property name
     *
     * @return Schema property
     */
    public function getProperty($name)
    {
        return $this->properties[$name];
    }

    /**
     * get properties
     *
     * @return Schema[]|null
     */
    public function getProperties()
    {
        $properties = $this->properties;
        if (empty($properties)) {
            $properties = null;
        }

        return $properties;
    }

    /**
     * set required variables
     *
     * @param string[] $required arary of required fields
     *
     * @return void
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }

    /**
     * get required fields
     *
     * @return string[]|null
     */
    public function getRequired()
    {
        $required = $this->required;
        if (empty($required)) {
            $required = null;
        }

        return $required;
    }

    /**
     * set translatable flag
     *
     * This flag is a local extension to json schema.
     *
     * @param boolean $translatable translatable flag
     *
     * @return void
     */
    public function setTranslatable($translatable)
    {
        $this->translatable = $translatable;
    }

    /**
     * get translatable flag
     *
     * @return boolean
     */
    public function isTranslatable()
    {
        return $this->translatable;
    }
}
