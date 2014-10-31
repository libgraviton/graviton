<?php
namespace Graviton\GeneratorBundle\Definition;

use Exception;

/**
 * This class represents the json file that defines the structure
 * of a mongo collection that exists and serves as a base to generate
 * a bundle.
 *
 * @todo     if this json format serves in more places; move this class
 * @todo     validate json
 *
 * @category GeneratorBundle
 * @package  Graviton
 * @author   Dario Nuevo <dario.nuevo@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class JsonDefinition
{

    /**
     * Path to our json file
     *
     * @var string
     */
    private $filename;

    /**
     * Deserialized json
     *
     * @var \stdClass
     */
    private $doc;

    /**
     * Constructor
     *
     * @param string $filename Path to the json file
     *
     * @throws Exception
     */
    public function __construct($filename)
    {
        $this->filename = $filename;

        if (!file_exists($this->filename)) {
            throw new Exception(
                sprintf(
                    'File %s doesn\'t exist',
                    $this->filename
                )
            );
        }

        $this->doc = json_decode(file_get_contents($this->filename));
    }

    /**
     * Returns this loads ID
     *
     * @return string ID
     */
    public function getId()
    {
        return $this->doc->id;
    }

    /**
     * Returns the description
     *
     * @return string Description
     */
    public function getDescription()
    {
        return $this->doc->description;
    }

    /**
     * Returns a specific field or null
     *
     * @param string $name Field name
     *
     * @return JsonDefinitionField The field
     */
    public function getField($name)
    {
        $ret = null;
        foreach ($this->getFields() as $field) {
            if ($field->getName() == $name) {
                $ret = $field;
                break;
            }
        }

        return $ret;
    }

    /**
     * Returns the field definition
     *
     * @return JsonDefinitionField[] Fields
     */
    public function getFields()
    {
        $fields = array();
        foreach ($this->doc->target->fields as $field) {
            $field = new JsonDefinitionField($field);
            $fields[$field->getName()] = $field;
        }

        // object generation (dot-notation parsing)
        $fieldHierarchy = array();
        $retFields = array();
        foreach ($fields as $fieldName => $field) {
            if (
                strpos(
                    $fieldName,
                    '.'
                ) !== false
            ) {
                $nameParts = explode(
                    '.',
                    $fieldName
                );

                // hm, i'm too uninspired to make this recursive..
                switch (count($nameParts)) {
                    case 2:
                        $fieldHierarchy[$nameParts[0]][$nameParts[1]] = $field;
                        break;
                    case 3:
                        $fieldHierarchy[$nameParts[0]][$nameParts[1]][$nameParts[2]] = $field;
                        break;
                }
            } else {
                $retFields[$fieldName] = $field;
            }
        }

        foreach ($fieldHierarchy as $fieldName => $subElements) {
            $retFields[$fieldName] = new JsonDefinitionHash(
                $fieldName,
                $subElements
            );
        }

        return $retFields;
    }
}
