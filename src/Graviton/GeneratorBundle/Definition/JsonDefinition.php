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
        $ret = '';
        if (isset($this->doc->description)) {
            $ret = $this->doc->description;
        }

        return $ret;
    }

    /**
     * Returns whether this service is read-only
     *
     * @return bool true if yes, false if not
     */
    public function isReadOnlyService()
    {
        // default
        $ret = false;

        if (isset($this->doc->service->readOnly) && (bool) $this->doc->service->readOnly === true) {
            $ret = true;
        }

        return $ret;
    }

    /**
     * Returns whether this service has fixtures
     *
     * @param string $fieldName ask for specific field
     *
     * @return bool true if yes, false if not
     */
    public function hasFixtures($fieldName = null)
    {
        // default
        $ret = false;

        if (count($this->getFixtures($fieldName)) > 0) {
            $ret = true;
        }

        return $ret;
    }

    /**
     * Returns the fixtures or empty array if none
     *
     * @return array fixtures
     */
    public function getFixtures()
    {
        // default
        $ret = array();

        if (isset($this->doc->service->fixtures) && is_array($this->doc->service->fixtures)) {
            $ret = $this->doc->service->fixtures;
        }

        return $ret;
    }

    /**
     * Returns a router base path. false if default should be used.
     *
     * @return string router base, i.e. /bundle/name/
     */
    public function getRouterBase()
    {
        $ret = false;

        if (isset($this->doc->service->routerBase) && strlen($this->doc->service->routerBase) > 0) {
            $ret = $this->doc->service->routerBase;
            if (substr($ret, 0, 1) != '/') {
                $ret = '/' . $ret;
            }

            if (substr($ret, -1) == '/') {
                $ret = substr($ret, 0, -1);
            }
        }

        return $ret;
    }

    /**
     * Returns the Controller classname this services' controller shout inherit.
     * Defaults to the RestController of the RestBundle of course.
     *
     * @return string base controller
     */
    public function getBaseController()
    {
        $ret = 'RestController';

        if (isset($this->doc->service->baseController) && strlen($this->doc->service->baseController) > 0) {
            $ret = $this->doc->service->baseController;
        }

        return $ret;
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
        $arrayHashes = array();
        foreach ($fields as $fieldName => $field) {
            if (strpos($fieldName, '.') !== false) {
                $nameParts = explode('.', $fieldName);

                // hm, i'm too uninspired to make this recursive..
                switch (count($nameParts)) {
                    case 2:
                        $fieldHierarchy[$nameParts[0]][$nameParts[1]] = $field;

                        if (preg_match('([0-9]+)', $nameParts[1])) {
                            $arrayHashes[] = $nameParts[0];
                        }

                        break;
                    case 3:
                        // handle "0-9" in second part (like field.0.val)
                        // ..handle as normal hash, but set array property
                        if (preg_match('([0-9]+)', $nameParts[1])) {
                            $fieldHierarchy[$nameParts[0]][$nameParts[2]] = $field;
                            $arrayHashes[] = $nameParts[0];
                        } else {
                            $fieldHierarchy[$nameParts[0]][$nameParts[1]][$nameParts[2]] = $field;
                        }
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
            $retFields[$fieldName]->setParentName($this->getId());

            if (in_array($fieldName, $arrayHashes)) {
                $retFields[$fieldName]->setIsArrayHash(true);
            }
        }

        return $retFields;
    }
}
