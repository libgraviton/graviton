<?php
/**
 * JsonDefinitionHashTest class file
 */

namespace Graviton\GeneratorBundle\Tests\Definition;

use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Graviton\GeneratorBundle\Definition\JsonDefinitionHash;
use Graviton\GeneratorBundle\Definition\Schema;

/**
 * JsonDefinitionHash test
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class JsonDefinitionHashTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test JsonDefinitionHash::getName()
     *
     * @return void
     */
    public function testGetName()
    {
        $name = __METHOD__;
        $parent = new JsonDefinition(new Schema\Definition());

        $field = new JsonDefinitionHash($name, $parent, []);
        $this->assertEquals($name, $field->getName());
    }

    /**
     * Test JsonDefinitionHash::getType()
     *
     * @return void
     */
    public function testGetType()
    {
        $parent = new JsonDefinition(new Schema\Definition());

        $field = new JsonDefinitionHash('name', $parent, []);
        $this->assertEquals(JsonDefinitionHash::TYPE_HASH, $field->getType());
    }

    /**
     * Test JsonDefinitionHash::getTypeDoctrine()
     *
     * @return void
     */
    public function testGetTypeDoctrine()
    {
        $name = __FUNCTION__.__LINE__;
        $id = __FUNCTION__.__LINE__;
        $namespace = __NAMESPACE__;

        $parent = new JsonDefinition((new Schema\Definition())->setId($id));
        $parent->setNamespace($namespace);

        $field = new JsonDefinitionHash($name, $parent, []);
        $this->assertEquals(
            $namespace.'\\Document\\'.ucfirst($id).ucfirst($name),
            $field->getTypeDoctrine()
        );
    }

    /**
     * Test JsonDefinitionHash::getTypeSerializer()
     *
     * @return void
     */
    public function testGetTypeSerializer()
    {
        $name = __FUNCTION__.__LINE__;
        $id = __FUNCTION__.__LINE__;
        $namespace = __NAMESPACE__;

        $parent = new JsonDefinition((new Schema\Definition())->setId($id));
        $parent->setNamespace($namespace);

        $field = new JsonDefinitionHash($name, $parent, []);
        $this->assertEquals(
            $namespace.'\\Document\\'.ucfirst($id).ucfirst($name),
            $field->getTypeSerializer()
        );
    }

    /**
     * Test JsonDefinitionHash::getDefAsArray()
     *
     * @return void
     */
    public function testGetDefAsArray()
    {
        $name = __FUNCTION__.__LINE__;
        $id = __FUNCTION__.__LINE__;
        $namespace = __NAMESPACE__;

        $parent = new JsonDefinition((new Schema\Definition())->setId($id));
        $parent->setNamespace($namespace);

        $field = new JsonDefinitionHash($name, $parent, []);
        $this->assertEquals(
            [
                'name'              => $field->getName(),
                'type'              => $field->getType(),
                'exposedName'       => $field->getName(),
                'doctrineType'      => $field->getTypeDoctrine(),
                'serializerType'    => $field->getTypeSerializer(),
                'relType'           => JsonDefinitionHash::REL_TYPE_EMBED,
                'isClassType'       => true,
                'constraints'       => [],
                'required'          => true,
            ],
            $field->getDefAsArray()
        );
    }

    /**
     * Test JsonDefinitionHash::getDefAsArray() with field definition
     *
     * @return void
     */
    public function testGetDefAsArrayWithDefinition()
    {
        $name = __FUNCTION__.__LINE__;
        $id = __FUNCTION__.__LINE__;
        $namespace = __NAMESPACE__;

        $parent = new JsonDefinition((new Schema\Definition())->setId($id));
        $parent->setNamespace($namespace);

        $definition = (new Schema\Field())
            ->setTitle(__FUNCTION__.__LINE__)
            ->setDescription(__FUNCTION__.__LINE__)
            ->setExposeAs(__FUNCTION__.__LINE__)
            ->setReadOnly(true)
            ->setRequired(true);

        $field = new JsonDefinitionHash($name, $parent, [], $definition);
        $this->assertEquals(
            [
                'name'              => $field->getName(),
                'type'              => $field->getType(),
                'exposedName'       => $definition->getExposeAs(),
                'doctrineType'      => $field->getTypeDoctrine(),
                'serializerType'    => $field->getTypeSerializer(),
                'relType'           => JsonDefinitionHash::REL_TYPE_EMBED,
                'isClassType'       => true,
                'constraints'       => [],

                'title'             => $definition->getTitle(),
                'description'       => $definition->getDescription(),
                'readOnly'          => $definition->getReadOnly(),
                'required'          => $definition->getRequired(),
            ],
            $field->getDefAsArray()
        );
    }

    /**
     * Test JsonDefinitionHash::getJsonDefinition()
     *
     * @return void
     */
    public function testGetJsonDefinition()
    {
        $parent = new JsonDefinition(
            (new Schema\Definition())
                ->setId('Parent')
                ->setTarget(
                    (new Schema\Target())
                        ->setFields(
                            [
                                (new Schema\Field())
                                    ->setName('hash')
                                    ->setType('hash')
                                    ->setDescription('description'),
                                (new Schema\Field())
                                    ->setName('hash.b')
                                    ->setType('class:B'),
                                (new Schema\Field())
                                    ->setName('hash.c')
                                    ->setType('class:C[]'),
                                (new Schema\Field())
                                    ->setName('hash.d')
                                    ->setType(JsonDefinitionHash::TYPE_BOOLEAN),
                            ]
                        )
                        ->setRelations(
                            [
                                (new Schema\Relation())
                                    ->setType(JsonDefinitionHash::REL_TYPE_EMBED)
                                    ->setLocalProperty('hash.b'),
                                (new Schema\Relation())
                                    ->setType(JsonDefinitionHash::REL_TYPE_REF)
                                    ->setLocalProperty('hash.c')
                            ]
                        )
                )
        );

        /** @var JsonDefinitionHash $field */
        $field = $parent->getField('hash');
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinitionHash', $field);

        $this->assertEquals(
            new JsonDefinition(
                (new Schema\Definition())
                    ->setId('ParentHash')
                    ->setDescription('description')
                    ->setIsSubDocument(true)
                    ->setTarget(
                        (new Schema\Target())
                            ->setFields(
                                [
                                    (new Schema\Field())
                                        ->setName('b')
                                        ->setType('class:B'),
                                    (new Schema\Field())
                                        ->setName('c')
                                        ->setType('class:C[]'),
                                    (new Schema\Field())
                                        ->setName('d')
                                        ->setType(JsonDefinitionHash::TYPE_BOOLEAN),
                                ]
                            )
                            ->setRelations(
                                [
                                    (new Schema\Relation())
                                        ->setType(JsonDefinitionHash::REL_TYPE_EMBED)
                                        ->setLocalProperty('b'),
                                    (new Schema\Relation())
                                        ->setType(JsonDefinitionHash::REL_TYPE_REF)
                                        ->setLocalProperty('c')
                                ]
                            )
                    )
            ),
            $field->getJsonDefinition()
        );
    }
}
