<?php
/**
 * test json definition element
 */

namespace Graviton\GeneratorBundle\Tests\Definition;

use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Graviton\GeneratorBundle\Definition\JsonDefinitionHash;
use Graviton\GeneratorBundle\Definition\Schema\Constraint;
use Graviton\GeneratorBundle\Definition\Schema\ConstraintOption;
use JMS\Serializer\SerializerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DefinitionElementTest extends \PHPUnit_Framework_TestCase
{

    private $fullDefPath;

    /**
     * setup
     *
     * @return void
     */
    public function setUp()
    {
        $this->fullDefPath = __DIR__.'/resources/test-full.json';
    }

    /**
     * @param string $file Definition file path
     * @return JsonDefinition
     */
    private function loadJsonDefinition($file)
    {
        $serializer = SerializerBuilder::create()
            ->addDefaultHandlers()
            ->addDefaultSerializationVisitors()
            ->addDefaultDeserializationVisitors()
            ->addMetadataDir(__DIR__.'/../../Resources/config/serializer', 'Graviton\\GeneratorBundle')
            ->setCacheDir(sys_get_temp_dir())
            ->setDebug(true)
            ->build();

        return new JsonDefinition(
            $serializer->deserialize(
                file_get_contents($file),
                'Graviton\\GeneratorBundle\\Definition\\Schema\\Definition',
                'json'
            )
        );
    }

    /**
     * basics
     *
     * @return void
     */
    public function testBasics()
    {
        $jsonDef = $this->loadJsonDefinition($this->fullDefPath);
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinition', $jsonDef);

        $field = $jsonDef->getField('testField');
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinitionField', $field);
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\Schema\Field', $field->getDef());

        $this->assertEquals('testField', $field->getName());
        $this->assertEquals('A lengthy and detailed description.', $field->getDescription());
        $this->assertEquals('varchar', $field->getType());
        $this->assertEquals(200, $field->getLength());
        $this->assertEquals('string', $field->getTypeDoctrine());
        $this->assertEquals('string', $field->getTypeSerializer());
        $this->assertNull($field->getClassName());
        $this->assertNull($field->getParentHash());
        $this->assertFalse($field->isClassType());
        $this->assertTrue($field->isField());
        $this->assertFalse($field->isHash());
    }

    /**
     * arraydef
     *
     * @return void
     */
    public function testArrayDef()
    {
        $jsonDef = $this->loadJsonDefinition($this->fullDefPath);
        $field = $jsonDef->getField('testField');

        $def = array(
            'name' => 'testField',
            'type' => 'varchar',
            'length' => 200,
            'title' => 'A testing title',
            'description' => 'A lengthy and detailed description.',
            'exposeAs' => null,
            'readOnly' => false,
            'required' => true,
            'translatable' => true,
            'exposedName' => 'testField',
            'doctrineType' => 'string',
            'serializerType' => 'string',
            'relType' => $field::REL_TYPE_REF,
            'isClassType' => false,
            'constraints' => array()
        );

        $this->assertEquals($def, $field->getDefAsArray());
    }

    /**
     * class type
     *
     * @return void
     */
    public function testClassType()
    {
        $jsonDef = $this->loadJsonDefinition($this->fullDefPath);
        $field = $jsonDef->getField('contact');

        $this->assertTrue($field->isClassType());
        $this->assertEquals('Graviton\PersonBundle\Document\PersonContact', $field->getClassName());
        $this->assertEquals('Graviton\PersonBundle\Document\PersonContact', $field->getType());
        $this->assertEquals('Graviton\PersonBundle\Document\PersonContact', $field->getTypeSerializer());
        $this->assertEquals('Graviton\PersonBundle\Document\PersonContact', $field->getTypeDoctrine());

        $field = $jsonDef->getField('contacts');
        $this->assertEquals('Graviton\PersonBundle\Document\PersonContact[]', $field->getClassName());
        $this->assertEquals('Graviton\PersonBundle\Document\PersonContact[]', $field->getType());
        $this->assertEquals('array<Graviton\PersonBundle\Document\PersonContact>', $field->getTypeSerializer());
        $this->assertEquals('Graviton\PersonBundle\Document\PersonContact[]', $field->getTypeDoctrine());
    }

    /**
     * wrong type handling
     *
     * @return void
     */
    public function testWrongType()
    {
        // test fallback to string..
        $jsonDef = $this->loadJsonDefinition($this->fullDefPath);
        $field = $jsonDef->getField('unknownType');
        $this->assertEquals('unknown', $field->getType());
        $this->assertEquals('string', $field->getTypeSerializer());
        $this->assertEquals('string', $field->getTypeDoctrine());
    }

    /**
     * expose as
     *
     * @return void
     */
    public function testExposeAs()
    {
        $jsonDef = $this->loadJsonDefinition($this->fullDefPath);
        $field = $jsonDef->getField('unknownType');
        $this->assertEquals('unknown', $field->getExposedName());
    }

    /**
     * constraints return
     *
     * @return void
     */
    public function testConstraints()
    {
        $jsonDef = $this->loadJsonDefinition($this->fullDefPath);
        $field = $jsonDef->getField('emailField');

        $constraint = (new Constraint())
            ->setName('Email')
            ->setOptions(
                [
                    (new ConstraintOption())
                        ->setName('strict')
                        ->setValue('true')
                ]
            );

        $this->assertEquals([$constraint], $field->getConstraints());
    }

    /**
     * hash
     *
     * @return void
     */
    public function testHash()
    {
        $jsonDef = $this->loadJsonDefinition($this->fullDefPath);

        /** @var JsonDefinitionHash $field */
        $field = $jsonDef->getField('contactCode');
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinitionHash', $field);

        $this->assertEquals($field::REL_TYPE_EMBED, $field->getRelType());
        $this->assertTrue($field->isClassType());
        $this->assertTrue($field->isHash());
        $this->assertFalse($field->isField());
        $this->assertEquals(array('datetime', 'varchar'), $field->getFieldTypes());
    }

    /**
     * array def
     *
     * @return void
     */
    public function testHashArrayDef()
    {
        $jsonDef = $this->loadJsonDefinition($this->fullDefPath);

        $field = $jsonDef->getField('contactCode');
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinitionHash', $field);

        $def = array(
            'exposedName' => 'contactCode',
            'type' => 'hash',
            'relType' => $field::REL_TYPE_EMBED,
            'doctrineType' => '\Document\ShowcaseContactCode',
            'serializerType' => '\Document\ShowcaseContactCode',
            'isClassType' => true
        );

        $this->assertEquals($def, $field->getDefAsArray());
    }

    /**
     * local def
     *
     * @return void
     */
    public function testHashLocalDef()
    {
        $jsonDef = $this->loadJsonDefinition($this->fullDefPath);

        /** @var JsonDefinitionHash $field */
        $field = $jsonDef->getField('contactCode');
        $localDef = $field->getDefFromLocal();
        $this->assertTrue($localDef->isSubDocument());
        $this->assertEquals(count($field->getFields()), count($localDef->getFields()));

        // array
        $field = $jsonDef->getField('nestedArray');
        $localDef = $field->getDefFromLocal();
        $this->assertTrue($localDef->isSubDocument());
        $this->assertEquals(count($field->getFields()), count($localDef->getFields()));
    }

    /**
     * bop
     *
     * @return void
     */
    public function testBagOfPrimitives()
    {
        // @todo bag of primitive support is not finished; i'm locking it down, it isn't used anyway
        $jsonDef = $this->loadJsonDefinition($this->fullDefPath);

        /** @var JsonDefinitionHash $field */
        $field = $jsonDef->getField('bag');
        $this->assertTrue($field->isBagOfPrimitives());
        $this->assertEquals('varchar', $field->getClassName());
    }

    /**
     * reltype
     *
     * @return void
     */
    public function testSetRelType()
    {
        $jsonDef = $this->loadJsonDefinition($this->fullDefPath);
        $field = $jsonDef->getField('contact');
        $this->assertEquals($field::REL_TYPE_REF, $field->getRelType());

        $field->setRelType($field::REL_TYPE_EMBED);
        $this->assertEquals($field::REL_TYPE_EMBED, $field->getRelType());
    }
}
