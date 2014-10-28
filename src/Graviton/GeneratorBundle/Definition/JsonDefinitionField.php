<?php
namespace Graviton\GeneratorBundle\Definition;

use \Exception;

/**
 * A single field as specified in the json definition
 *
 * @todo if this json format serves in more places; move this class
 *      
 * @category GeneratorBundle
 * @package Graviton
 * @author Dario Nuevo <dario.nuevo@swisscom.com>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link http://swisscom.ch
 */
class JsonDefinitionField
{

    /**
     * Consts for comparison
     *
     * @var string
     */
    const TYPE_STRING = 'VARCHAR';

    const TYPE_INTEGER = 'INT';

    /**
     * Our definition
     *
     * @var \stdClass
     */
    private $_def;

    /**
     * Constructor
     *
     * @param \stdClass $def
     *            Definition
     */
    public function __construct($def)
    {
        $this->_def = $def;
    }

    /**
     * Returns the field name
     *
     * @return string Name
     */
    public function getName()
    {
        return $this->_def->name;
    }

    /**
     * Returns the field type
     *
     * @return string Type
     */
    public function getType()
    {
        return $this->_def->type;
    }

    /**
     * Returns the field length
     *
     * @return int length
     */
    public function getLength()
    {
        return $this->_def->length;
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
        if (isset($this->_def->description))
            $ret = $this->_def->description;
        
        return $ret;
    }
}
