<?php
namespace Graviton\GeneratorBundle\Definition;

/**
 * Represents a hash of fields as defined in the JSON format
 *
 * @category GeneratorBundle
 * @package  Graviton
 * @author   Dario Nuevo <dario.nuevo@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class JsonDefinitionHash implements DefinitionElementInterface
{

    /**
     * Array of fields..
     *
     * @var JsonDefinitionField[]
     */
    private $fields = array();

    /**
     * Name of this hash
     *
     * @var string
     */
    private $name;

    /**
     * Name of the parent definition (needed in name composing)
     *
     * @var string
     */
    private $parentName;

    /**
     * Constructor
     *
     * @param string                $name   Name of this hash
     * @param JsonDefinitionField[] $fields Fields of the hash
     */
    public function __construct($name, array $fields)
    {
        $this->name = $name;

        // sets ourselves as parent on our fields
        foreach ($fields as $key => $field) {
            $fields[$key]->setParentHash($this);
        }

        $this->fields = $fields;
    }

    /**
     * {@inheritDoc}
     *
     * @return bool
     */
    public function isField()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     *
     * @return bool
     */
    public function isHash()
    {
        return true;
    }

    /**
     * Returns the types of all fields
     *
     * @return string[] the types..
     */
    public function getFieldTypes()
    {
        $ret = array();
        foreach ($this->getFields() as $field) {
            $ret[] = $field->getType();
        }

        return $ret;
    }

    /**
     * Returns this hash' fields..
     *
     * @return array|JsonDefinitionField[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Returns the definition as array..
     *
     * @return string[] the definition
     */
    public function getDefAsArray()
    {
        return array(
            'type' => $this->getType(),
            'doctrineType' => $this->getTypeDoctrine(),
            'serializerType' => $this->getClassName(true),
            'isClassType' => true
        );
    }

    /**
     * {@inheritDoc}
     *
     * @return string type
     */
    public function getType()
    {
        return self::TYPE_HASH;
    }

    /**
     * {@inheritDoc}
     *
     * @return string type
     */
    public function getTypeDoctrine()
    {
        return $this->getClassName(true);
    }

    /**
     * Returns whether this is a class type (= not a primitive)
     *
     * @return boolean true if yes
     */
    public function isClassType()
    {
        return true;
    }

    /**
     * Returns the field definition of this hash from "local perspective",
     * meaning that we only include fields inside this hash BUT with all
     * the stuff from the json file. this is needed to generate a Document/Model
     * from this hash (generate a json file again)
     *
     * @return array the definition of this hash in a standalone array ready to be json_encoded()
     */
    public function getDefFromLocal()
    {
        $ret = array();
        $ret['id'] = $this->getClassName();
        $ret['target']['fields'] = array();

        foreach ($this->getFields() as $field) {
            $thisDef = clone $field->getDef();
            $thisDef->name = str_replace($this->getName() . '.', '', $thisDef->name);

            $ret['target']['fields'][] = (array) $thisDef;
        }

        return $ret;
    }

    /**
     * Returns the class name of this hash, possibly
     * taking the parent element into the name. this
     * string here results in the name of the generated Document.
     *
     * @param boolean $fq if true, we'll return the class name full qualified
     *
     * @return string
     */
    public function getClassName($fq = false)
    {
        $ret = ucfirst($this->getName());
        if (!is_null($this->getParentName())) {
            $ret = $this->getParentName() . $ret;
        }

        if (true === $fq) {
            $ret = 'GravitonDyn\ShowcaseBundle\Document\\' . $ret;
        }

        return $ret;
    }

    /**
     * Returns the hash name
     *
     * @return string Name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the name of parent definition element
     *
     * @return string parent name
     */
    public function getParentName()
    {
        return $this->parentName;
    }

    /**
     * Sets the parent name
     *
     * @param string $parentName parent name
     *
     * @return void
     */
    public function setParentName($parentName)
    {
        $this->parentName = $parentName;
    }
}
