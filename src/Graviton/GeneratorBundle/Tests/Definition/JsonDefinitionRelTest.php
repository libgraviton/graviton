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
class JsonDefinitionRelTest extends \PHPUnit_Framework_TestCase
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
        $relation = (new Schema\Relation())
            ->setType(__METHOD__.__LINE__);

        $field = new JsonDefinitionRel('name', $definition, $relation);
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
                'relType'           => $relation->getType(),
                'isClassType'       => true,
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
