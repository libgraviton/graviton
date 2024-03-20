<?php
/**
 * JsonDefinitionRelTest class file
 */

namespace Graviton\Tests\Generator\Definition;

use Graviton\GeneratorBundle\Definition\JsonDefinitionRel;
use Graviton\GeneratorBundle\Definition\Schema;
use Graviton\Tests\Generator\Definition\JsonDefinitionFieldTestAbstract;

/**
 * JsonDefinitionRel test
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class JsonDefinitionRelTest extends JsonDefinitionFieldTestAbstract
{
    /**
     * Test JsonDefinitionRel::getName()
     *
     * @return void
     */
    public function testGetName()
    {
        $name = __METHOD__;
        $definition = new Schema\Field();

        $field = new JsonDefinitionRel($name, $definition);
        $this->assertEquals($name, $field->getName());
    }

    /**
     * Test JsonDefinitionRel::getType()
     *
     * @return void
     */
    public function testGetType()
    {
        $type = __METHOD__;
        $definition = (new Schema\Field())->setType('class:'.$type);

        $field = new JsonDefinitionRel('name', $definition);
        $this->assertEquals($type, $field->getType());
    }

    /**
     * Test JsonDefinitionRel::getTypeDoctrine()
     *
     * @return void
     */
    public function testGetTypeDoctrine()
    {
        $type = __METHOD__;
        $definition = (new Schema\Field())->setType('class:'.$type);

        $field = new JsonDefinitionRel('name', $definition);
        $this->assertEquals($type, $field->getTypeDoctrine());
    }

    /**
     * Test JsonDefinitionRel::getTypeSerializer()
     *
     * @return void
     */
    public function testGetTypeSerializer()
    {
        $type = __METHOD__;
        $definition = (new Schema\Field())->setType('class:'.$type);

        $field = new JsonDefinitionRel('name', $definition);
        $this->assertEquals($type, $field->getTypeSerializer());
    }

    /**
     * Test JsonDefinitionRel::getDefAsArray()
     *
     * @return void
     */
    public function testGetDefAsArray()
    {
        $definition = $this->getBaseField();
        $relation = (new Schema\Relation())
            ->setType(__METHOD__.__LINE__);

        $field = new JsonDefinitionRel('name', $definition, $relation);
        $this->assertEquals(
            array_replace(
                $this->getBaseDefAsArray($definition),
                [
                    'name'                  => $field->getName(),
                    'type'                  => $field->getType(),
                    'exposedName'           => $definition->getExposeAs(),
                    'doctrineType'          => $field->getTypeDoctrine(),
                    'serializerType'        => $field->getTypeSerializer(),
                    'schemaType'            => $field->getTypeSchema(),
                    'relType'               => $relation->getType(),
                    'isClassType'           => true,
                    'searchable'            => 0,
                    'recordOriginException' => false,
                    'hidden'                => false,
                    'valuePattern'          => null
                ]
            ),
            $field->getDefAsArray()
        );
    }
}
