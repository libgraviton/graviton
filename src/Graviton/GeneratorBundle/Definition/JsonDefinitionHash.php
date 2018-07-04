<?php
namespace Graviton\GeneratorBundle\Definition;
use Graviton\GeneratorBundle\Definition\Schema\Field;

/**
 * Represents a hash of fields as defined in the JSON format
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
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
     * @var Schema\Field Field definition
     */
    private $definition;

    /**
     * Constructor
     *
     * @param string                       $name       Name of this hash
     * @param JsonDefinition               $parent     Parent definiton
     * @param DefinitionElementInterface[] $fields     Fields of the hash
     * @param Schema\Field                 $definition Field definition
     */
    public function __construct($name, JsonDefinition $parent, array $fields, Schema\Field $definition = null)
    {
        $this->name = $name;
        $this->parent = $parent;
        $this->fields = $fields;
        $this->definition = $definition;
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
     * Returns the definition as array..
     *
     * @return array the definition
     */
    public function getDefAsArray()
    {
        return array_replace(
            [
                'name'              => $this->getName(),
                'type'              => $this->getType(),
                'exposedName'       => $this->getName(),
                'doctrineType'      => $this->getTypeDoctrine(),
                'serializerType'    => $this->getTypeSerializer(),
                'relType'           => self::REL_TYPE_EMBED,
                'isClassType'       => true,
                'constraints'       => [],
                'required'          => false,
                'searchable'        => 0,
            ],
            $this->definition === null ? [
                'required'          => $this->isRequired()
            ] : [
                'exposedName'       => $this->definition->getExposeAs() ?: $this->getName(),
                'title'             => $this->definition->getTitle(),
                'description'       => $this->definition->getDescription(),
                'readOnly'          => $this->definition->getReadOnly(),
                'required'          => $this->definition->getRequired(),
                'searchable'        => $this->definition->getSearchable(),
                'constraints'       => array_map(
                    [Utils\ConstraintNormalizer::class, 'normalize'],
                    $this->definition->getConstraints()
                ),
            ]
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
     * Whether this hash is anonymous, so has no own field definition (properly only defined
     * by definitions such as "object.field", not an own definition)
     *
     * @return bool true if yes, false otherwise
     */
    public function isAnonymous()
    {
        return ($this->definition === null);
    }

    /**
     * in an 'anonymous' hash situation, we will check if any children are required
     *
     * @return bool if required or not
     */
    public function isRequired()
    {
        $isRequired = false;

        // see if on the first level of fields we have a required=true in the definition
        foreach ($this->fields as $field) {
            if ($field instanceof JsonDefinitionField &&
                $field->getDef() instanceof Field &&
                $field->getDef()->getRequired() === true
            ) {
                $isRequired = true;
            }
        }

        return $isRequired;
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
            ->setDescription($this->definition === null ? null : $this->definition->getDescription())
            ->setTitle($this->definition === null ? null : $this->definition->getTitle())
            ->setIsSubDocument(true)
            ->setTarget(new Schema\Target());

        foreach ($this->fields as $field) {
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
            return [$this->cloneFieldDefinition($field->getDef())];
        } elseif ($field instanceof JsonDefinitionArray) {
            return $this->processFieldDefinitionsRecursive($field->getElement());
        } elseif ($field instanceof JsonDefinitionHash) {
            return array_reduce(
                $field->fields,
                function (array $subfields, DefinitionElementInterface $subfield) {
                    return array_merge($subfields, $this->processFieldDefinitionsRecursive($subfield));
                },
                $field->definition === null ? [] : [$this->cloneFieldDefinition($field->definition)]
            );
        }

        throw new \InvalidArgumentException(sprintf('Unknown field type "%s"', get_class($field)));
    }

    /**
     * Clone field definition
     *
     * @param Schema\Field $field Field
     * @return Schema\Field
     */
    private function cloneFieldDefinition(Schema\Field $field)
    {
        $clone = clone $field;
        $clone->setName(preg_replace('/^'.preg_quote($this->name, '/').'\.(\d+\.)*/', '', $clone->getName()));
        return $clone;
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
