<?php
/**
 * JsonDefinitionFieldTest class file
 */

namespace Graviton\GeneratorBundle\Tests\Definition;

use Graviton\DocumentBundle\Entity\ExtReference;
use Graviton\DocumentBundle\Entity\Hash;
use Graviton\GeneratorBundle\Definition\JsonDefinitionField;
use Graviton\GeneratorBundle\Definition\Schema;

/**
 * JsonDefinitionField test
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class JsonDefinitionFieldTest extends BaseJsonDefinitionFieldTest
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
            JsonDefinitionField::TYPE_OBJECT => 'hash',
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
            JsonDefinitionField::TYPE_OBJECT => Hash::class,
            JsonDefinitionField::TYPE_EXTREF => ExtReference::class,
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
        $definition = $this->getBaseField();

        $field = new JsonDefinitionField('name', $definition);
        $this->assertEquals(
            array_replace(
                $this->getBaseDefAsArray($definition),
                [
                    'name'                  => $field->getName(),
                    'type'                  => $field->getType(),
                    'exposedName'           => $definition->getExposeAs(),
                    'doctrineType'          => $field->getTypeDoctrine(),
                    'serializerType'        => $field->getTypeSerializer(),
                    'relType'               => null,
                    'isClassType'           => false,
                    'xDynamicKey'           => null,
                    'searchable'            => 0,
                    'recordOriginException' => false,
                    'hideOnEmptyExtref'     => false
                ]
            ),
            $field->getDefAsArray()
        );
    }

    /**
     * Test JsonDefinitionField::getXDynamicKey()
     *
     * @return void
     */
    public function testGetXDynamicKey()
    {
        $key = (new Schema\XDynamicKey())
            ->setDocumentId(__CLASS__)
            ->setRepositoryMethod(__LINE__)
            ->setRefField(__FILE__);

        $definition = (new Schema\Field())
            ->setXDynamicKey($key);

        $field = new JsonDefinitionField('name', $definition);

        $this->assertEquals(
            array_replace(
                $this->getBaseDefAsArray($definition),
                [
                    'name'                  => $field->getName(),
                    'type'                  => $field->getType(),
                    'exposedName'           => $field->getName(),
                    'doctrineType'          => $field->getTypeDoctrine(),
                    'serializerType'        => $field->getTypeSerializer(),
                    'relType'               => null,
                    'isClassType'           => false,
                    'xDynamicKey'           => $key,
                    'searchable'            => 0,
                    'recordOriginException' => false,
                    'hideOnEmptyExtref'     => false
                ]
            ),
            $field->getDefAsArray()
        );
    }
}
