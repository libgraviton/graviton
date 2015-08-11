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
            'readOnly'          => $this->definition->getReadOnly(),
            'required'          => $this->definition->getRequired(),
            'translatable'      => $this->definition->getTranslatable(),
            'collection'        => $this->definition->getCollection(),

            'name'              => $this->getName(),
            'type'              => $this->getType(),
            'exposedName'       => $this->getExposedName(),
            'doctrineType'      => $this->getTypeDoctrine(),
            'serializerType'    => $this->getTypeSerializer(),
            'relType'           => null,
            'isClassType'       => false,
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
                $this->definition->getConstraints()
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
    private function getExposedName()
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
        return strtolower($this->definition->getType());
    }

    /**
     * Returns the field type in a serializer-understandable way..
     *
     * @return string Type
     */
    public function getTypeSerializer()
    {
        if (isset($this->serializerTypeMap[$this->getType()])) {
            return $this->serializerTypeMap[$this->getType()];
        }

        // our fallback default
        return $this->serializerTypeMap[self::TYPE_STRING];
    }
}
