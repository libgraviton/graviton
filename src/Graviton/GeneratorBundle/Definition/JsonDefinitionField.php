<?php
namespace Graviton\GeneratorBundle\Definition;

/**
 * A single field as specified in the json definition
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class JsonDefinitionField implements DefinitionElementInterface
{
    /**
     * Typemap from our source types to doctrine types
     */
    private $doctrineTypeMap = [
        self::TYPE_STRING => 'string',
        self::TYPE_INTEGER => 'int',
        self::TYPE_LONG => 'int',
        self::TYPE_DOUBLE => 'float',
        self::TYPE_DECIMAL => 'float',
        self::TYPE_DATETIME => 'date',
        self::TYPE_BOOLEAN => 'boolean',
        self::TYPE_OBJECT => 'object',
        self::TYPE_EXTREF => 'extref',
    ];

    private $serializerTypeMap = [
        self::TYPE_STRING => 'string',
        self::TYPE_INTEGER => 'integer',
        self::TYPE_LONG => 'integer',
        self::TYPE_DOUBLE => 'double',
        self::TYPE_DECIMAL => 'double',
        self::TYPE_DATETIME => 'DateTime',
        self::TYPE_BOOLEAN => 'boolean',
        self::TYPE_OBJECT => 'array',
        self::TYPE_EXTREF => 'string',
    ];

    /**
     * @var string
     */
    private $name;
    /**
     * Our definition
     *
     * @var Schema\Field
     */
    private $definition;

    /**
     * How the relation type of this field is (if applicable to the type)
     *
     * @var string rel type
     */
    private $relType = self::REL_TYPE_REF;

    /**
     * Constructor
     *
     * @param string       $name       Field name
     * @param Schema\Field $definition Definition
     */
    public function __construct($name, Schema\Field $definition)
    {
        $this->name = $name;
        $this->definition = $definition;
    }

    /**
     * Returns the field definition
     *
     * @return Schema\Field definition
     */
    public function getDef()
    {
        return $this->definition;
    }

    /**
     * Returns the field name
     *
     * @return string Name
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
        return [
            'length'            => $this->definition->getLength(),
            'title'             => $this->definition->getTitle(),
            'description'       => $this->definition->getDescription(),
            'exposeAs'          => $this->definition->getExposeAs(),
            'readOnly'          => $this->definition->getReadOnly(),
            'required'          => $this->definition->getRequired(),
            'translatable'      => $this->definition->getTranslatable(),
            'collection'        => $this->definition->getCollection(),

            'name'              => $this->getName(),
            'type'              => $this->getType(),
            'exposedName'       => $this->getExposedName(),
            'doctrineType'      => $this->getTypeDoctrine(),
            'serializerType'    => $this->getTypeSerializer(),
            'relType'           => $this->getRelType(),
            'isClassType'       => $this->isClassType(),
            'constraints'       => array_map(
                function (Schema\Constraint $constraint) {
                    return [
                        'name'  => $constraint->getName(),
                        'options'   => array_map(
                            function (Schema\ConstraintOption $option) {
                                return [
                                    'name'  => $option->getName(),
                                    'value' => $option->getValue(),
                                ];
                            },
                            $constraint->getOptions()
                        )
                    ];
                },
                $this->getConstraints()
            )
        ];
    }

    /**
     * Returns the field type in a doctrine-understandable way..
     *
     * @return string Type
     */
    public function getTypeDoctrine()
    {
        if ($this->isClassType()) {
            return $this->getClassName();
        }

        if (isset($this->doctrineTypeMap[$this->getType()])) {
            return $this->doctrineTypeMap[$this->getType()];
        }

        // our fallback default
        return $this->doctrineTypeMap[self::TYPE_STRING];
    }

    /**
     * Gets the name this field should be exposed as (serializer concern).
     * Normally this is the name, but can be overriden by "exposeAs" property on the field.
     *
     * @return string exposed field name
     */
    public function getExposedName()
    {
        return $this->definition->getExposeAs() === null ?
            $this->definition->getName() :
            $this->definition->getExposeAs();
    }

    /**
     * Returns the field type
     *
     * @return string Type
     */
    public function getType()
    {
        if ($this->isClassType()) {
            return $this->getClassName();
        }

        return strtolower($this->definition->getType());
    }

    /**
     * Returns the field type in a serializer-understandable way..
     *
     * @return string Type
     */
    public function getTypeSerializer()
    {
        if ($this->isClassType()) {
            $className = $this->getClassName();
            if (substr($className, -2) === '[]') {
                return 'array<'.substr($className, 0, -2).'>';
            }

            return $className;
        }

        if (isset($this->serializerTypeMap[$this->getType()])) {
            return $this->serializerTypeMap[$this->getType()];
        }

        // our fallback default
        return $this->serializerTypeMap[self::TYPE_STRING];
    }

    /**
     * If this is a classType, return the defined class name
     *
     * @return string class name
     */
    public function getClassName()
    {
        if ($this->isClassType()) {
            return str_replace('class:', '', $this->definition->getType());
        }

        return null;
    }

    /**
     * Returns whether this is a class type (= not a primitive)
     *
     * @return boolean true if yes
     */
    public function isClassType()
    {
        return strpos($this->definition->getType(), 'class:') === 0;
    }

    /**
     * Returns the field length
     *
     * @return int length
     */
    public function getLength()
    {
        return $this->definition->getLength();
    }

    /**
     * Returns defined Constraints for this field
     *
     * @return Schema\Constraint[] Constraints
     */
    public function getConstraints()
    {
        return $this->definition->getConstraints();
    }

    /**
     * Returns the field description
     *
     * @return string description
     */
    public function getDescription()
    {
        return $this->definition->getDescription() === null ?
            '' :
            $this->definition->getDescription();
    }

    /**
     * Gets the rel type
     *
     * @return string
     */
    public function getRelType()
    {
        return $this->relType;
    }

    /**
     * Sets the rel type
     *
     * @param string $relType rel type
     *
     * @return void
     */
    public function setRelType($relType)
    {
        $this->relType = $relType;
    }
}
