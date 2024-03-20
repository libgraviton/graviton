<?php
/**
 * test json definition element
 */

namespace Graviton\Tests\Generator\Definition;

use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Graviton\GeneratorBundle\Definition\JsonDefinitionArray;
use Graviton\GeneratorBundle\Definition\JsonDefinitionField;
use Graviton\GeneratorBundle\Definition\JsonDefinitionHash;
use Graviton\GeneratorBundle\Definition\JsonDefinitionRel;
use Graviton\Tests\Generator\Utils;
use Graviton\Tests\GravitonTestCase;
use JMS\Serializer\SerializerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class DefinitionElementTest extends GravitonTestCase
{

    private $fullDefPath;

    /**
     * setup
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->fullDefPath = __DIR__.'/resources/test-full.json';
    }

    /**
     * @param string $file Definition file path
     * @return JsonDefinition
     */
    private function loadJsonDefinition($file)
    {
        $serializer = Utils::getSerializerInstance();

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

        /** @var JsonDefinitionField $field */
        $field = $jsonDef->getField('testField');
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinitionField', $field);
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\Schema\Field', $field->getDef());

        $subArray = [
            'name'              => $field->getName(),
            'exposedName'       => 'testField',
            'type'              => 'translatable',
            'doctrineType'      => 'translatable',
            'serializerType'    => 'Graviton\DocumentBundle\Entity\Translatable',
            'isClassType'       => false,
            'relType'           => null,
            'description'       => 'A lengthy and detailed description.',
            'length'            => 200,
            'readOnly'          => false,
        ];

        $def = $field->getDefAsArray();

        foreach ($subArray as $key => $val) {
            $this->assertEquals($val, $def[$key], $key);
        }
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
            'type' => 'translatable',
            'length' => 200,
            'title' => 'A testing title',
            'description' => 'A lengthy and detailed description.',
            'readOnly' => false,
            'required' => true,
            'translatable' => true,
            'exposedName' => 'testField',
            'doctrineType' => 'translatable',
            'serializerType' => 'Graviton\DocumentBundle\Entity\Translatable',
            'schemaType' => '#/components/schemas/GravitonTranslatable',
            'relType' => null,
            'isClassType' => false,
            'constraints' => [],
            'collection' => [],
            'searchable' => 1,
            'recordOriginException' => false,
            'hidden' => false,
            'valuePattern' => null
        );

        $this->assertEquals($def, $field->getDefAsArray());
    }

    /**
     * class type
     *
     * @return void
     */
    public function testEmbedFields()
    {
        $jsonDef = $this->loadJsonDefinition($this->fullDefPath);

        /** @var JsonDefinitionRel $embedField */
        $embedField = $jsonDef->getField('contact');
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinitionRel', $embedField);
        $this->assertEquals('GravitonDyn\PersonContactBundle\Document\PersonContact', $embedField->getType());
        $this->assertEquals('GravitonDyn\PersonContactBundle\Document\PersonContact', $embedField->getTypeSerializer());
        $this->assertEquals('GravitonDyn\PersonContactBundle\Document\PersonContact', $embedField->getTypeDoctrine());

        /** @var JsonDefinitionArray $arrayField */
        $arrayField = $jsonDef->getField('contacts');
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinitionArray', $arrayField);
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinitionRel', $arrayField->getElement());
        $this->assertEquals('GravitonDyn\PersonContactBundle\Document\PersonContact[]', $arrayField->getType());
        $this->assertEquals(
            'array<GravitonDyn\PersonContactBundle\Document\PersonContact>',
            $arrayField->getTypeSerializer()
        );
        $this->assertEquals('GravitonDyn\PersonContactBundle\Document\PersonContact[]', $arrayField->getTypeDoctrine());
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

        /** @var JsonDefinitionField $field */
        $field = $jsonDef->getField('unknownType');
        $this->assertEquals(
            'unknown',
            $field->getDefAsArray()['exposedName']
        );
    }

    /**
     * constraints return
     *
     * @return void
     */
    public function testConstraints()
    {
        $jsonDef = $this->loadJsonDefinition($this->fullDefPath);

        /** @var JsonDefinitionField $field */
        $field = $jsonDef->getField('emailField');

        $this->assertEquals(
            [
                [
                    'name' => 'Email',
                    'options' => [
                        [
                            'name' => 'strict',
                            'value' => 'true',
                        ],
                    ],
                ]
            ],
            $field->getDefAsArray()['constraints']
        );
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

        $this->assertEquals(
            'datetime',
            $field->getJsonDefinition()->getField('someDate')->getType()
        );
        $this->assertEquals(
            'translatable',
            $field->getJsonDefinition()->getField('text')->getType()
        );
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

        $this->assertEquals(
            [
                'type' => 'hash',
                'name' => 'contactCode',
                'exposedName' => 'contactCode',
                'relType' => $field::REL_TYPE_EMBED,
                'doctrineType' => '\Document\ShowcaseContactCode',
                'serializerType' => '\Document\ShowcaseContactCode',
                'schemaType' => 'class:\Document\ShowcaseContactCode',
                'isClassType' => true,
                'constraints' => [],
                'required' => true,
                'searchable' => 0
            ],
            $field->getDefAsArray()
        );
    }

    /**
     * local def
     *
     * @return void
     */
    public function testHashLocalDef()
    {
        $jsonDef = $this->loadJsonDefinition($this->fullDefPath);

        /** @var JsonDefinitionHash $hashField */
        $hashField = $jsonDef->getField('contactCode');
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinitionHash', $hashField);

        /** @var JsonDefinitionArray $arrayField */
        $arrayField = $jsonDef->getField('nestedArray');
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinitionArray', $arrayField);

        /** @var JsonDefinitionHash $arrayItem */
        $arrayItem = $arrayField->getElement();
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinitionHash', $arrayItem);
    }
}
