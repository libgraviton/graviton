<?php
namespace Graviton\GeneratorBundle\Definition;

use \Exception;

/**
 * This class represents the json file that defines the structure
 * of a mongo collection that exists and serves as a base to generate
 * a bundle.
 *
 * @todo if this json format serves in more places; move this class
 * @todo validate json
 *      
 * @category GeneratorBundle
 * @package Graviton
 * @author Dario Nuevo <dario.nuevo@swisscom.com>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link http://swisscom.ch
 */
class JsonDefinition
{

    /**
     * Path to our json file
     *
     * @var string
     */
    private $_filename;

    /**
     * Deserialized json
     *
     * @var \stdClass
     */
    private $_doc;

    /**
     * Constructor
     *
     * @param string $filename
     *            Path to the json file
     * @throws Exception
     */
    public function __construct($filename)
    {
        $this->_filename = $filename;
        
        if (! file_exists($this->_filename)) {
            throw new Exception(sprintf('File %s doesn\'t exist', $this->_filename));
        }
        
        $this->_doc = json_decode(file_get_contents($this->_filename));
    }

    /**
     * Returns the field definition
     *
     * @return JsonDefinitionField[]
     */
    public function getFields()
    {
        $ret = array();
        foreach ($this->_doc->target->fields as $field) {
            $ret[] = new JsonDefinitionField($field);
        }
        return $ret;
    }
}
