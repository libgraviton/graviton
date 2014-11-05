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
     * Returns the hash name
     *
     * @return string Name
     */
    public function getName()
    {
        return $this->name;
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
     * Returns the types of all fields
     *
     * @return string[] the types..
     */
    public function getFieldDoctrineTypes()
    {
        $ret = array();

        foreach ($this->getFields() as $field) {
            if ($field instanceof JsonDefinitionField) {
                $ret[] = $field->getTypeDoctrine();
            } elseif (is_array($field)) {
                // @todo how to prepare arrays of hashes here for the serializer?
                $ret[] = 'string';
            }
        }

        return $ret;
    }

    /**
     * Returns the definition as array..
     *
     * @return string[] the definiton
     */
    public function getDefAsArray()
    {
        return array(
            'type' => $this->getType(),
            'doctrineType' => $this->getTypeDoctrine(),
            'serializerType' => 'array'
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
        return self::TYPE_HASH;
    }
}
