<?php
/**
 * JsonDefinitionRelTest class file
 */

namespace Graviton\GeneratorBundle\Tests\Definition;

use Graviton\GeneratorBundle\Definition\JsonDefinitionRel;
use Graviton\GeneratorBundle\Definition\Schema;

/**
 * JsonDefinitionRel test
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class JsonDefinitionRelTest extends BaseJsonDefinitionFieldTest
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
                    'relType'               => $relation->getType(),
                    'isClassType'           => true,
                    'searchable'            => 0,
                    'recordOriginException' => false
                ]
            ),
            $field->getDefAsArray()
        );
    }
}
