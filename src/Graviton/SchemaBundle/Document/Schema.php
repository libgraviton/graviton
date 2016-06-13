<?php
/**
 * Graviton Schema Document
 */

namespace Graviton\SchemaBundle\Document;

use \Doctrine\Common\Collections\ArrayCollection;

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
     * @var ArrayCollection
     */
    protected $properties;

    /**
     * @var SchemaAdditionalProperties
     */
    protected $additionalProperties;

    /**
     * @var string[]
     */
    protected $required = array();

    /**
     * @var boolean
     */
    protected $translatable;

    /**
     * @var array
     */
    protected $refCollection = array();

    /**
     * possible event names this collection implements (queue events)
     *
     * @var array
     */
    protected $eventNames = array();

    /**
     * @var bool
     */
    protected $readOnly = false;

    /**
     * @var string[]
     */
    protected $searchable = array();

    /**
     * @var int
     */
    protected $minLength;

    /**
     * @var int
     */
    protected $maxLength;

    /**
     * @var SchemaEnum
     */
    protected $enum;

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
    protected $specialTypeMapping = [
        'extref' => 'string',
        'translatable' => 'object',
        'date' => 'string',
        'float' => 'number',
        'double' => 'number',
        'decimal' => 'number'
    ];

    protected $formatOverrides = [
        'date' => 'date-time'
    ];

    /**
     * Build properties
     */
    public function __construct()
    {
        $this->properties = new ArrayCollection();
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

        // handle non-primitive types
        if (!in_array($type, $this->primitiveTypes)) {
            $setType = 'string';
            if (isset($this->specialTypeMapping[$type])) {
                $setType = $this->specialTypeMapping[$type];
            }
            $this->type = $setType;

            if (isset($this->formatOverrides[$type])) {
                $type = $this->formatOverrides[$type];
            }

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
     * set min length
     *
     * @return int length
     */
    public function getMinLength()
    {
        return $this->minLength;
    }

    /**
     * get min length
     *
     * @param int $minLength length
     *
     * @return void
     */
    public function setMinLength($minLength)
    {
        $this->minLength = $minLength;
    }

    /**
     * gets maxlength
     *
     * @return int length
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }

    /**
     * set maxlength
     *
     * @param int $maxLength length
     *
     * @return void
     */
    public function setMaxLength($maxLength)
    {
        $this->maxLength = $maxLength;
    }

    /**
     * get Enum
     *
     * @return array Enum
     */
    public function getEnum()
    {
        return $this->enum;
    }

    /**
     * set Enum
     *
     * @param array $enum enum
     *
     * @return void
     */
    public function setEnum(array $enum)
    {
        $this->enum = new SchemaEnum($enum);
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
        $this->properties->set($name, $property);
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
        if (!$this->properties->containsKey($name)) {
            $this->properties->remove($this->properties->get($name));
        }
    }

    /**
     * returns a property
     *
     * @param string $name property name
     *
     * @return void|Schema property
     */
    public function getProperty($name)
    {
        return $this->properties->get($name);
    }

    /**
     * get properties
     *
     * @return ArrayCollection|null
     */
    public function getProperties()
    {
        if ($this->properties->isEmpty()) {
            return null;
        } else {
            return $this->properties;
        }
    }

    /**
     * set additionalProperties on schema
     *
     * @param SchemaAdditionalProperties $additionalProperties additional properties
     *
     * @return void
     */
    public function setAdditionalProperties(SchemaAdditionalProperties $additionalProperties)
    {
        $this->additionalProperties = $additionalProperties;
    }

    /**
     * get addtionalProperties for schema
     *
     * @return SchemaAdditionalProperties
     */
    public function getAdditionalProperties()
    {
        return $this->additionalProperties;
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

    /**
     * set a array of urls that can extref refer to
     *
     * @param array $refCollection urls
     *
     * @return void
     */
    public function setRefCollection(array $refCollection)
    {
        $this->refCollection = $refCollection;
    }

    /**
     * get a collection of urls that can extref refer to
     *
     * @return array
     */
    public function getRefCollection()
    {
        $collection = $this->refCollection;
        if (empty($collection)) {
            $collection = null;
        }

        return $collection;
    }

    /**
     * set an array of possible event names
     *
     * @param array $eventNames event names
     *
     * @return void
     */
    public function setEventNames(array $eventNames)
    {
        $this->eventNames = array_values($eventNames);
    }

    /**
     * get a collection of possible event names
     *
     * @return array
     */
    public function getEventNames()
    {
        $collection = $this->eventNames;
        if (empty($collection)) {
            $collection = null;
        }

        return $collection;
    }

    /**
     * Set the readOnly flag
     *
     * @param bool $readOnly ReadOnly flag
     *
     * @return void
     */
    public function setReadOnly($readOnly)
    {
        $this->readOnly = (bool) $readOnly;
    }

    /**
     * Get the readOnly flag.
     * Returns null if the flag is set to false so the serializer will ignore it.
     *
     * @return bool|null true if readOnly isset to true or null if not
     */
    public function getReadOnly()
    {
        return $this->readOnly ? true : null;
    }

    /**
     * set searchable variables
     *
     * @param string[] $searchable arary of searchable fields
     *
     * @return void
     */
    public function setSearchable($searchable)
    {
        $this->searchable = $searchable;
    }

    /**
     * get searchable fields
     *
     * @return string[]|null
     */
    public function getSearchable()
    {
        $searchable = $this->searchable;
        if (empty($searchable)) {
            $searchable = null;
        }

        return $searchable;
    }
}
