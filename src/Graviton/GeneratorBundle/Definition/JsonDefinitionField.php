<?php
namespace Graviton\GeneratorBundle\Definition;

/**
 * A single field as specified in the json definition
 *
 * @todo     if this json format serves in more places; move this class
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
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
        self::TYPE_DOUBLE => 'float',
        self::TYPE_DECIMAL => 'float',
        self::TYPE_DATETIME => 'date',
        self::TYPE_BOOLEAN => 'boolean',
        self::TYPE_OBJECT => 'object'
    );

    private $serializerTypeMap = array(
        self::TYPE_STRING => 'string',
        self::TYPE_INTEGER => 'integer',
        self::TYPE_LONG => 'integer',
        self::TYPE_DOUBLE => 'double',
        self::TYPE_DECIMAL => 'double',
        self::TYPE_DATETIME => 'DateTime',
        self::TYPE_BOOLEAN => 'boolean',
        self::TYPE_OBJECT => 'array'
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
     * How the relation type of this field is (if applicable to the type)
     *
     * @var string rel type
     */
    private $relType = self::REL_TYPE_REF;

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
        $ret['exposedName'] = $this->getExposedName();
        $ret['doctrineType'] = $this->getTypeDoctrine();
        $ret['serializerType'] = $this->getTypeSerializer();
        $ret['relType'] = $this->getRelType();
        $ret['isClassType'] = $this->isClassType();
        $ret['constraints'] = $this->getConstraints();

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
     * Gets the name this field should be exposed as (serializer concern).
     * Normally this is the name, but can be overriden by "exposeAs" property on the field.
     *
     * @return string exposed field name
     */
    public function getExposedName()
    {
        $ret = $this->def->name;

        if (isset($this->def->exposeAs) && strlen($this->def->exposeAs) > 0) {
            $ret = $this->def->exposeAs;
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
        return preg_match('/^class\:/', $this->def->type) > 0;
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
     * Returns defined Constraints for this field
     *
     * @return array Constraints
     */
    public function getConstraints()
    {
        $ret = array();
        if (isset($this->def->constraints) && is_array($this->def->constraints)) {
            $ret = $this->def->constraints;
        }
        return $ret;
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

    /**
     * Gets the rel type
     *
     * @return string
     */
    public function getRelType()
    {
        return $this->relType;
    }

    /**
     * Sets the rel type
     *
     * @param string $relType rel type
     *
     * @return void
     */
    public function setRelType($relType)
    {
        $this->relType = $relType;
    }
}
