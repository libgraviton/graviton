<?php
/**
 * Graviton Schema Document
 */

namespace Graviton\SchemaBundle\Document;

/**
 * Graviton\SchemaBundle\Document\Schema
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class Schema
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
     * @var string
     */
    protected $format;

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
     * these are the BSON primitive types.
     * http://json-schema.org/latest/json-schema-core.html#anchor8
     * every type set *not* in this set will be carried over to 'format'
     *
     * @var string[]
     */
    protected $primitiveTypes = array(
        'array',
        'boolean',
        'integer',
        'number',
        'null',
        'object',
        'string'
    );

    /**
     * known non-primitive types we map to primitives here.
     * the type itself is set to the format.
     *
     * @var string[]
     */
    protected $specialTypeMapping = array(
        'extref' => 'string',
        'translatable' => 'object'
    );

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

        // handle non-primitive types
        if (!in_array($type, $this->primitiveTypes)) {
            $setType = 'string';
            if (isset($this->specialTypeMapping[$type])) {
                $setType = $this->specialTypeMapping[$type];
            }
            $this->type = $setType;
            $this->setFormat($type);
        } else {
            $this->type = $type;
        }
    }

    /**
     * get type
     *
     * @return string type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * get format
     *
     * @return string format
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * sets format
     *
     * @param string $format format
     *
     * @return void
     */
    public function setFormat($format)
    {
        $this->format = $format;
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
     * @param string $name property name
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
     * @param string $name property name
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
        if ($translatable === true) {
            $this->setType('translatable');
        } else {
            $this->setType('string');
        }
    }

    /**
     * get translatable flag
     *
     * @return boolean
     */
    public function isTranslatable()
    {
        $ret = false;
        if ($this->getFormat() == 'translatable') {
            $ret = true;
        }

        return $ret;
    }
}
