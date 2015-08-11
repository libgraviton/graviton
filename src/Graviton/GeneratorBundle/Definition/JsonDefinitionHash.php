<?php
namespace Graviton\GeneratorBundle\Definition;

/**
 * Represents a hash of fields as defined in the JSON format
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class JsonDefinitionHash implements DefinitionElementInterface
{
    /**
     * @var string Name of this hash
     */
    private $name;
    /**
     * @var JsonDefinition
     */
    private $parent;
    /**
     * @var DefinitionElementInterface[] Array of fields..
     */
    private $fields = [];

    /**
     * Constructor
     *
     * @param string                       $name   Name of this hash
     * @param JsonDefinition               $parent Parent definiton
     * @param DefinitionElementInterface[] $fields Fields of the hash
     */
    public function __construct($name, JsonDefinition $parent, array $fields)
    {
        $this->name = $name;
        $this->parent = $parent;
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
     * Returns this hash' fields..
     *
     * @return DefinitionElementInterface[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Returns the definition as array..
     *
     * @return array the definition
     */
    public function getDefAsArray()
    {
        return [
            'name'              => $this->getName(),
            'type'              => $this->getType(),

            'exposedName'       => $this->getName(),
            'doctrineType'      => $this->getTypeDoctrine(),
            'serializerType'    => $this->getTypeSerializer(),
            'relType'           => self::REL_TYPE_EMBED,
            'isClassType'       => true,
            'constraints'       => [],
        ];
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
     * Returns the field type in a serializer-understandable way..
     *
     * @return string Type
     */
    public function getTypeSerializer()
    {
        return $this->getClassName(true);
    }

    /**
     * Returns the field definition of this hash from "local perspective",
     * meaning that we only include fields inside this hash BUT with all
     * the stuff from the json file. this is needed to generate a Document/Model
     * from this hash (generate a json file again)
     *
     * @return JsonDefinition the definition of this hash in a standalone array ready to be json_encoded()
     */
    public function getJsonDefinition()
    {
        $definition = (new Schema\Definition())
            ->setId($this->getClassName())
            ->setIsSubDocument(true)
            ->setTarget(new Schema\Target());

        foreach ($this->getFields() as $field) {
            foreach ($this->processFieldDefinitionsRecursive($field) as $definitions) {
                $definition->getTarget()->addField($definitions);
            }
        }
        foreach ($this->parent->getRelations() as $relation) {
            $relation = $this->processParentRelation($relation);
            if ($relation !== null) {
                $definition->getTarget()->addRelation($relation);
            }
        }

        return new JsonDefinition($definition);
    }

    /**
     * Method getFieldDefinitionsRecursive
     *
     * @param DefinitionElementInterface $field
     * @return Schema\Field[]
     */
    private function processFieldDefinitionsRecursive(DefinitionElementInterface $field)
    {
        if ($field instanceof JsonDefinitionField) {
            $clone = clone $field->getDef();
            $clone->setName(preg_replace('/^'.preg_quote($this->name, '/').'\.(\d+\.)*/', '', $clone->getName()));

            return [$clone];
        } elseif ($field instanceof JsonDefinitionArray) {
            return $this->processFieldDefinitionsRecursive($field->getElement());
        } elseif ($field instanceof JsonDefinitionHash) {
            return array_reduce(
                $field->fields,
                function (array $subfields, DefinitionElementInterface $subfield) {
                    return array_merge($subfields, $this->processFieldDefinitionsRecursive($subfield));
                },
                []
            );
        }

        throw new \InvalidArgumentException(sprintf('Unknown field type "%s"', get_class($field)));
    }

    /**
     * Process parent relation
     *
     * @param Schema\Relation $relation Parent relation
     * @return Schema\Relation|null
     */
    private function processParentRelation(Schema\Relation $relation)
    {
        $prefixRegex = '/^'.preg_quote($this->name, '/').'\.(\d+\.)*(?P<sub>.*)/';
        if (!preg_match($prefixRegex, $relation->getLocalProperty(), $matches)) {
            return null;
        }

        $clone = clone $relation;
        $clone->setLocalProperty($matches['sub']);
        return $clone;
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
    private function getClassName($fq = false)
    {
        $className = ucfirst($this->parent->getId()).ucfirst($this->getName());
        if ($fq) {
            $className = $this->parent->getNamespace().'\\Document\\'.$className;
        }

        return $className;
    }
}
