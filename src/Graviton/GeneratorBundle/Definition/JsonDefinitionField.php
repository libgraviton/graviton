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
        self::TYPE_INTEGER => 'integer',
        self::TYPE_DATETIME => 'date'
    );

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
        return $this->def->type;
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
