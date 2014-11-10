<?php
namespace Graviton\GeneratorBundle\Definition;

/**
 * A single field as specified in the json definition
 *
 * @todo     if this json format serves in more places; move this class
 *
 * @category GeneratorBundle
 * @package  Graviton
 * @author   Dario Nuevo <dario.nuevo@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class JsonDefinitionField implements DefinitionElementInterface
{

    /**
     * Typemap from our source types to doctrine types
     */
    private $doctrineTypeMap = array(
        self::TYPE_STRING => 'string',
        self::TYPE_INTEGER => 'int',
        self::TYPE_LONG => 'int',
        self::TYPE_DATETIME => 'date',
        self::TYPE_BOOLEAN => 'boolean'
    );

    private $serializerTypeMap = array(
        self::TYPE_STRING => 'string',
        self::TYPE_INTEGER => 'integer',
        self::TYPE_LONG => 'integer',
        self::TYPE_DATETIME => 'DateTime',
        self::TYPE_BOOLEAN => 'boolean'
    );

    /**
     * This is a ref to the parent hash of this field (if any)
     *
     * @var JsonDefinitionHash
     */
    private $parentHash;

    /**
     * Our definition
     *
     * @var \stdClass
     */
    private $def;

    /**
     * Constructor
     *
     * @param \stdClass $def Definition
     */
    public function __construct($def)
    {
        $this->def = $def;
    }

    /**
     * Returns the definition
     *
     * @return \stdClass definition
     */
    public function getDef()
    {
        return $this->def;
    }

    /**
     * Returns the field name
     *
     * @return string Name
     */
    public function getName()
    {
        return $this->def->name;
    }

    /**
     * Returns the whole definition in array form
     *
     * @return array Definition
     */
    public function getDefAsArray()
    {
        $ret = (array) $this->def;
        $ret['doctrineType'] = $this->getTypeDoctrine();
        $ret['serializerType'] = $this->getTypeSerializer();
        $ret['isClassType'] = $this->isClassType();

        return $ret;
    }

    /**
     * Returns the field type in a doctrine-understandable way..
     *
     * @return string Type
     */
    public function getTypeDoctrine()
    {
        if ($this->isClassType()) {
            $ret = $this->getClassName();
        } else {
            if (isset($this->doctrineTypeMap[$this->getType()])) {
                $ret = $this->doctrineTypeMap[$this->getType()];
            } else {
                // our fallback default
                $ret = $this->doctrineTypeMap[self::TYPE_STRING];
            }
        }

        return $ret;
    }

    /**
     * Returns the field type
     *
     * @return string Type
     */
    public function getType()
    {
        $thisType = $this->def->type;
        if ($this->isClassType()) {
            $thisType = $this->getClassName();
        } else {
            $thisType = strtolower($thisType);
        }

        return $thisType;
    }

    /**
     * Returns the field type in a serializer-understandable way..
     *
     * @return string Type
     */
    public function getTypeSerializer()
    {
        if ($this->isClassType()) {
            $ret = $this->getClassName();
            // collection?
            if (substr($ret, -2) == '[]') {
                $ret = 'array<'.substr($ret, 0, -2).'>';
            }
        } else {
            if (isset($this->serializerTypeMap[$this->getType()])) {
                $ret = $this->serializerTypeMap[$this->getType()];
            } else {
                // our fallback default
                $ret = $this->serializerTypeMap[self::TYPE_STRING];
            }
        }

        return $ret;
    }

    /**
     * If this is a classType, return the defined class name
     *
     * @return string class name
     */
    public function getClassName()
    {
        $ret = null;
        if ($this->isClassType()) {
            $ret = str_replace('class:', '', $this->def->type);
        }

        return $ret;
    }

    /**
     * Returns whether this is a class type (= not a primitive)
     *
     * @return boolean true if yes
     */
    public function isClassType()
    {
        return preg_match('/^class\:/', $this->def->type);
    }

    /**
     * Returns the field length
     *
     * @return int length
     */
    public function getLength()
    {
        return $this->def->length;
    }

    /**
     * Returns the field description
     *
     * @return string description
     */
    public function getDescription()
    {
        // not mandatory..
        $ret = '';
        if (isset($this->def->description)) {
            $ret = $this->def->description;
        }

        return $ret;
    }

    /**
     * Returns the parent hash (if any)
     *
     * @return JsonDefinitionHash The parent hash
     */
    public function getParentHash()
    {
        return $this->parentHash;
    }

    /**
     * Sets the parent hash
     *
     * @param JsonDefinitionHash $parentHash The parent hash
     *
     * @return void
     */
    public function setParentHash(JsonDefinitionHash $parentHash)
    {
        $this->parentHash = $parentHash;
    }

    /**
     * {@inheritDoc}
     *
     * @return bool
     */
    public function isField()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @return bool
     */
    public function isHash()
    {
        return false;
    }
}
