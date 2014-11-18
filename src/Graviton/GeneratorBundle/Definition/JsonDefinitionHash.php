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
     * Whether this is an array hash, so an array of ourselves.
     *
     * @var bool true if yes
     */
    private $isArrayHash = false;

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
            'serializerType' => $this->getTypeSerializer(),
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
        $ret = $this->getClassName(true);

        // make sure we're recognized as array ;-)
        if ($this->isArrayHash()) {
            $ret .= '[]';
        }

        return $ret;
    }

    /**
     * Returns the field type in a serializer-understandable way..
     *
     * @return string Type
     */
    public function getTypeSerializer()
    {
        $ret = $this->getClassName(true);

        // make sure we're recognized as array ;-)
        if ($this->isArrayHash()) {
            $ret = 'array<'.$ret.'>';
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
        return true;
    }

    /**
     * Well.. a "bag of primitives" is basically if we're having an array
     * (isArrayHash()=true) and we're only having primitive types
     * in our fields with NO keys(!)
     * get the difference: a hash forms an object with index keys
     * (i.e. {"hans": "fred"}, BUT with the same type and NO keys
     * we have a bag of primitives, ie. [3, 4, 5]
     *
     * @return boolean true if yes
     */
    public function isBagOfPrimitives()
    {
        $ret = true;
        foreach ($this->getFields() as $key => $field) {
            if (!preg_match('([0-9]+)', $key)) {
                $ret = false;
                break;
            }
        }
        return $ret;

    }

    /**
     * true if this is an array hash
     *
     * @return boolean
     */
    public function isArrayHash()
    {
        return $this->isArrayHash;
    }

    /**
     * set if this is an array hash
     *
     * @param boolean $isArrayHash if array hash or not
     *
     * @return boolean
     */
    public function setIsArrayHash($isArrayHash)
    {
        $this->isArrayHash = $isArrayHash;
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

            if ($this->isArrayHash()) {
                $thisDef->name = preg_replace('/([0-9]+)\./', '', $thisDef->name);
            }

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
        if (!$this->isBagOfPrimitives()) {
            $ret = ucfirst($this->getName());
            if (!is_null($this->getParentName())) {
                $ret = $this->getParentName() . $ret;
            }

            if (true === $fq) {
                $ret = 'GravitonDyn\ShowcaseBundle\Document\\' . $ret;
            }
        } else {
            // ok, we're a bag of primitives.. (ie int[] or string[])
            // let's just get the first field and take that
            $thisFields = $this->getFields();
            $firstField = array_shift($thisFields);

            $ret = $firstField->getType();
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
