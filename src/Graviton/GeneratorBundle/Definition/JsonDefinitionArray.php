<?php
namespace Graviton\GeneratorBundle\Definition;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class JsonDefinitionArray implements DefinitionElementInterface
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var DefinitionElementInterface
     */
    private $element;

    /**
     * Constructor
     *
     * @param string                     $name    Field name
     * @param DefinitionElementInterface $element Array item definition
     */
    public function __construct($name, DefinitionElementInterface $element)
    {
        $this->name = $name;
        $this->element = $element;
    }

    /**
     * @return DefinitionElementInterface
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Returns the name of this field
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the whole definition in array form
     *
     * @return array Definition
     */
    public function getDefAsArray()
    {
        return array_replace(
            $this->element->getDefAsArray(),
            [
                'name'              => $this->getName(),
                'type'              => $this->getType(),
                'doctrineType'      => $this->getTypeDoctrine(),
                'serializerType'    => $this->getTypeSerializer()
            ],
            $this->getHashAnonymousHashRequired()
        );
    }

    /**
     * Returns the field type in a doctrine-understandable way..
     *
     * @return string Type
     */
    public function getTypeDoctrine()
    {
        return $this->element->getTypeDoctrine().'[]';
    }

    /**
     * possible overrides. if the element is an anonymous hash, we will always
     * default to required => false.
     *
     * @return array additional overrides
     */
    private function getHashAnonymousHashRequired()
    {
       if ($this->element instanceof JsonDefinitionHash && $this->element->isAnonymous()) {
           return ['required' => false];
       }

       return [];
    }

    /**
     * Returns the field type
     *
     * @return string Type
     */
    public function getType()
    {
        return $this->element->getType().'[]';
    }

    /**
     * Returns the field type in a serializer-understandable way..
     *
     * @return string Type
     */
    public function getTypeSerializer()
    {
        return 'array<'.$this->element->getTypeSerializer().'>';
    }
}
