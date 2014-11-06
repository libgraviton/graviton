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

        return $ret;
    }

    /**
     * Returns the field type in a doctrine-understandable way..
     *
     * @return string Type
     */
    public function getTypeDoctrine()
    {
        $ret = false;
        if (isset($this->doctrineTypeMap[$this->getType()])) {
            $ret = $this->doctrineTypeMap[$this->getType()];
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
        return strtolower($this->def->type);
    }

    /**
     * Returns the field type in a serializer-understandable way..
     *
     * @return string Type
     */
    public function getTypeSerializer()
    {
        $ret = false;
        if (isset($this->serializerTypeMap[$this->getType()])) {
            $ret = $this->serializerTypeMap[$this->getType()];
        }

        return $ret;
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
