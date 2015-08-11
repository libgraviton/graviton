<?php
/**
 * JsonDefinitionFieldTest class file
 */

namespace Graviton\GeneratorBundle\Tests\Definition;

use Graviton\GeneratorBundle\Definition\JsonDefinitionField;
use Graviton\GeneratorBundle\Definition\Schema;

/**
 * JsonDefinitionField test
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class JsonDefinitionFieldTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test JsonDefinitionField::getDef()
     *
     * @return void
     */
    public function testGetDef()
    {
        $definition = new Schema\Field();

        $field = new JsonDefinitionField('name', $definition);
        $this->assertSame($definition, $field->getDef());
    }

    /**
     * Test JsonDefinitionField::getName()
     *
     * @return void
     */
    public function testGetName()
    {
        $name = __METHOD__;
        $definition = new Schema\Field();

        $field = new JsonDefinitionField($name, $definition);
        $this->assertEquals($name, $field->getName());
    }

    /**
     * Test JsonDefinitionField::getType()
     *
     * @return void
     */
    public function testGetType()
    {
        $definition = (new Schema\Field())->setType(__METHOD__);

        $field = new JsonDefinitionField('name', $definition);
        $this->assertEquals(strtolower($definition->getType()), $field->getType());
    }

    /**
     * Test JsonDefinitionField::getTypeDoctrine()
     *
     * @param string $defType      Definition type
     * @param string $doctrineType Doctrine type
     * @return void
     * @dataProvider dataGetTypeDoctrine
     */
    public function testGetTypeDoctrine($defType, $doctrineType)
    {
        $definition = (new Schema\Field())->setType($defType);

        $field = new JsonDefinitionField('name', $definition);
        $this->assertEquals($doctrineType, $field->getTypeDoctrine());
    }

    /**
     * Data provider for JsonDefinitionField::getTypeDoctrine()
     *
     * @return array
     */
    public function dataGetTypeDoctrine()
    {
        $map = [
            JsonDefinitionField::TYPE_STRING => 'string',
            JsonDefinitionField::TYPE_INTEGER => 'int',
            JsonDefinitionField::TYPE_LONG => 'int',
            JsonDefinitionField::TYPE_DOUBLE => 'float',
            JsonDefinitionField::TYPE_DECIMAL => 'float',
            JsonDefinitionField::TYPE_DATETIME => 'date',
            JsonDefinitionField::TYPE_BOOLEAN => 'boolean',
            JsonDefinitionField::TYPE_OBJECT => 'object',
            JsonDefinitionField::TYPE_EXTREF => 'extref',
        ];
        return array_map(
            function ($defType, $doctrineType) {
                return [$defType, $doctrineType];
            },
            array_keys($map),
            array_values($map)
        );
    }

    /**
     * Test JsonDefinitionField::getTypeSerializer()
     *
     * @param string $defType        Definition type
     * @param string $serializerType Serializer type
     * @return void
     * @dataProvider dataGetTypeSerializer
     */
    public function testGetTypeSerializer($defType, $serializerType)
    {
        $definition = (new Schema\Field())->setType($defType);

        $field = new JsonDefinitionField('name', $definition);
        $this->assertEquals($serializerType, $field->getTypeSerializer());
    }

    /**
     * Data provider for JsonDefinitionField::getTypeSerializer()
     *
     * @return array
     */
    public function dataGetTypeSerializer()
    {
        $map = [
            JsonDefinitionField::TYPE_STRING => 'string',
            JsonDefinitionField::TYPE_INTEGER => 'integer',
            JsonDefinitionField::TYPE_LONG => 'integer',
            JsonDefinitionField::TYPE_DOUBLE => 'double',
            JsonDefinitionField::TYPE_DECIMAL => 'double',
            JsonDefinitionField::TYPE_DATETIME => 'DateTime',
            JsonDefinitionField::TYPE_BOOLEAN => 'boolean',
            JsonDefinitionField::TYPE_OBJECT => 'array',
            JsonDefinitionField::TYPE_EXTREF => 'string',
        ];
        return array_map(
            function ($defType, $serializerType) {
                return [$defType, $serializerType];
            },
            array_keys($map),
            array_values($map)
        );
    }

    /**
     * Test JsonDefinitionField::getDefAsArray()
     *
     * @return void
     */
    public function testGetDefAsArray()
    {
        $definition = (new Schema\Field())
            ->setName(__METHOD__.__LINE__)
            ->setExposeAs(__METHOD__.__LINE__)
            ->setTitle(__METHOD__.__LINE__)
            ->setDescription(__METHOD__.__LINE__)
            ->setType(__METHOD__.__LINE__)
            ->setLength(__METHOD__.__LINE__)
            ->setReadOnly(__METHOD__.__LINE__)
            ->setTranslatable(__METHOD__.__LINE__)
            ->setRequired(__METHOD__.__LINE__)
            ->setCollection([__METHOD__.__LINE__])
            ->setConstraints(
                [
                    (new Schema\Constraint())
                        ->setName(__METHOD__.__LINE__)
                        ->setOptions(
                            [
                                (new Schema\ConstraintOption())
                                    ->setName(__METHOD__.__LINE__)
                                    ->setValue(__METHOD__.__LINE__),
                                (new Schema\ConstraintOption())
                                    ->setName(__METHOD__.__LINE__)
                                    ->setValue(__METHOD__.__LINE__),
                            ]
                        ),
                    (new Schema\Constraint())
                        ->setName(__METHOD__.__LINE__)
                        ->setOptions(
                            [
                                (new Schema\ConstraintOption())
                                    ->setName(__METHOD__.__LINE__)
                                    ->setValue(__METHOD__.__LINE__),
                                (new Schema\ConstraintOption())
                                    ->setName(__METHOD__.__LINE__)
                                    ->setValue(__METHOD__.__LINE__),
                            ]
                        ),
                ]
            );

        $field = new JsonDefinitionField('name', $definition);
        $this->assertEquals(
            [
                'length'            => $definition->getLength(),
                'title'             => $definition->getTitle(),
                'description'       => $definition->getDescription(),
                'readOnly'          => $definition->getReadOnly(),
                'required'          => $definition->getRequired(),
                'translatable'      => $definition->getTranslatable(),
                'collection'        => $definition->getCollection(),

                'name'              => $field->getName(),
                'type'              => $field->getType(),
                'exposedName'       => $definition->getExposeAs(),
                'doctrineType'      => $field->getTypeDoctrine(),
                'serializerType'    => $field->getTypeSerializer(),
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
                    $definition->getConstraints()
                ),
            ],
            $field->getDefAsArray()
        );
    }
}
