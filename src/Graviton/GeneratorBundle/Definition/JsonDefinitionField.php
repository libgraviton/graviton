<?php
namespace Graviton\GeneratorBundle\Definition;

use Graviton\DocumentBundle\Entity\ExtReference;
use Graviton\DocumentBundle\Entity\Hash;
use Graviton\DocumentBundle\Entity\Translatable;

/**
 * A single field as specified in the json definition
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class JsonDefinitionField implements DefinitionElementInterface
{
    /**
     * Typemap from our source types to doctrine types
     */
    private static $doctrineTypeMap = [
        self::TYPE_STRING => 'string',
        self::TYPE_VARCHAR => 'string',
        self::TYPE_TEXT => 'string',
        self::TYPE_INTEGER => 'int',
        self::TYPE_LONG => 'int',
        self::TYPE_FLOAT => 'decimal128',
        self::TYPE_DOUBLE => 'decimal128',
        self::TYPE_DECIMAL => 'decimal128',
        self::TYPE_DATETIME => 'date',
        self::TYPE_BOOLEAN => 'boolean',
        self::TYPE_OBJECT => 'hash',
        self::TYPE_EXTREF => 'extref',
        self::TYPE_TRANSLATABLE => 'translatable'
    ];

    private static $serializerTypeMap = [
        self::TYPE_STRING => 'string',
        self::TYPE_VARCHAR => 'string',
        self::TYPE_TEXT => 'string',
        self::TYPE_INTEGER => 'integer',
        self::TYPE_LONG => 'integer',
        self::TYPE_FLOAT => 'double',
        self::TYPE_DOUBLE => 'double',
        self::TYPE_DECIMAL => 'double',
        self::TYPE_DATETIME => 'DateTime',
        self::TYPE_BOOLEAN => 'boolean',
        self::TYPE_OBJECT => Hash::class,
        self::TYPE_EXTREF => ExtReference::class,
        self::TYPE_TRANSLATABLE => Translatable::class
    ];

    private static $schemaTypeMap = [
        self::TYPE_STRING => 'string',
        self::TYPE_VARCHAR => 'string',
        self::TYPE_TEXT => 'string',
        self::TYPE_INTEGER => 'integer',
        self::TYPE_LONG => 'integer',
        self::TYPE_FLOAT => 'number',
        self::TYPE_DOUBLE => 'number',
        self::TYPE_DECIMAL => 'number',
        self::TYPE_DATETIME => 'datetime',
        self::TYPE_BOOLEAN => 'boolean',
        self::TYPE_OBJECT => 'hash',
        self::TYPE_EXTREF => 'extref',
        self::TYPE_TRANSLATABLE => '#/components/schemas/GravitonTranslatable'
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
            'length'                => $this->definition->getLength(),
            'title'                 => $this->definition->getTitle(),
            'valuePattern'          => $this->definition->getValuePattern(),
            'description'           => $this->definition->getDescription(),
            'readOnly'              => $this->definition->getReadOnly(),
            'hidden'                => $this->definition->getHidden(),
            'recordOriginException' => $this->definition->isRecordOriginException(),
            'required'              => $this->definition->getRequired(),
            'searchable'            => $this->definition->getSearchable(),
            'translatable'          => $this->definition->getTranslatable(),
            'collection'            => $this->definition->getCollection(),

            'name'                  => $this->getName(),
            'type'                  => $this->getType(),
            'exposedName'           => $this->getExposedName(),
            'doctrineType'          => $this->getTypeDoctrine(),
            'serializerType'        => $this->getTypeSerializer(),
            'schemaType'            => $this->getTypeSchema(),
            'relType'               => null,
            'isClassType'           => false,
            'constraints'           => array_map(
                [Utils\ConstraintNormalizer::class, 'normalize'],
                $this->definition->getConstraints()
            ),
        ];
    }

    /**
     * Returns the field type in a doctrine-understandable way..
     *
     * @return string Type
     */
    public function getTypeDoctrine()
    {
        if (isset(self::$doctrineTypeMap[$this->getType()])) {
            return self::$doctrineTypeMap[$this->getType()];
        }

        // our fallback default
        return self::$doctrineTypeMap[self::TYPE_STRING];
    }

    /**
     * returns the field type in json schema
     *
     * @return string Type
     */
    public function getTypeSchema()
    {
        $type = $this->getType();

        if (str_ends_with($type, '[]')) {
            $type = substr($type, 0, -2);
        }

        if (isset(self::$schemaTypeMap[$type])) {
            $type = self::$schemaTypeMap[$type];
        } else {
            // classname?
            if (str_contains($type, '\\Document\\')) {
                $type = 'class:'.$type;
            } else {
                $type = 'string';
            }
        }

        return $type;
    }

    /**
     * Returns the field type
     *
     * @return string Type
     */
    public function getType()
    {
        if ($this->definition->getTranslatable()) {
            return self::TYPE_TRANSLATABLE;
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
        if (isset(self::$serializerTypeMap[$this->getType()])) {
            return self::$serializerTypeMap[$this->getType()];
        }

        // our fallback default
        return self::$serializerTypeMap[self::TYPE_STRING];
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
            $this->getName() :
            $this->definition->getExposeAs();
    }
}
