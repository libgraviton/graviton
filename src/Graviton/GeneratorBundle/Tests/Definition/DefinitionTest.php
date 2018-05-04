<?php
/**
 * test json definition
 */

namespace Graviton\GeneratorBundle\Tests\Definition;

use Graviton\GeneratorBundle\Definition\DefinitionElementInterface;
use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Graviton\GeneratorBundle\Definition\JsonDefinitionField;
use Graviton\GeneratorBundle\Definition\JsonDefinitionArray;
use Graviton\GeneratorBundle\Definition\JsonDefinitionHash;
use Graviton\GeneratorBundle\Definition\JsonDefinitionRel;
use Graviton\GeneratorBundle\Definition\Schema;
use JMS\Serializer\SerializerBuilder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class DefinitionTest extends \PHPUnit\Framework\TestCase
{
    private $fullDefPath;
    private $minimalPath;
    private $noIdPath;
    private $invalidPath;
    private $wrongUriPath;
    private $subDocumentPath;
    private $relationsPath;
    private $rolesPath;
    private $nestedFieldPath;
    private $nestedRelationsPath;

    /**
     * setup
     *
     * @return void
     */
    public function setUp()
    {
        $this->fullDefPath = __DIR__.'/resources/test-full.json';
        $this->minimalPath = __DIR__.'/resources/test-minimal.json';
        $this->noIdPath = __DIR__.'/resources/test-noid.json';
        $this->invalidPath = __DIR__.'/resources/test-invalid.json';
        $this->wrongUriPath = __DIR__.'/resources/test-minimal-wrong-uri.json';
        $this->subDocumentPath = __DIR__.'/resources/test-minimal-sub.json';
        $this->relationsPath = __DIR__.'/resources/test-minimal-relations.json';
        $this->rolesPath = __DIR__.'/resources/test-roles.json';
        $this->nestedFieldPath = __DIR__.'/resources/test-nested-fields.json';
        $this->nestedRelationsPath = __DIR__.'/resources/test-nested-relations.json';
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
     * invalid handling
     *
     * @expectedException \JMS\Serializer\Exception\RuntimeException
     *
     * @return void
     */
    public function testInvalidHandling()
    {
        $this->loadJsonDefinition($this->invalidPath);
    }

    /**
     * no id
     *
     * @expectedException \RuntimeException
     *
     * @return void
     */
    public function testNoId()
    {
        $this->loadJsonDefinition($this->noIdPath)->getId();
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
        $this->assertEquals('Showcase', $jsonDef->getId());
        $this->assertEquals('A service showcasing all of our generator features', $jsonDef->getDescription());
        $this->assertTrue($jsonDef->hasController());
        $this->assertTrue($jsonDef->hasFixtures());
        $this->assertFalse($jsonDef->isReadOnlyService());
    }

    /**
     * full
     *
     * @return void
     */
    public function testFull()
    {
        $jsonDef = $this->loadJsonDefinition($this->fullDefPath);
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinition', $jsonDef);

        // we only assert what we didn't assert in testBasics()
        $this->assertEquals(0, count($jsonDef->getRelations()));
        $this->assertEquals(16, count($jsonDef->getFields()));
        $this->assertEquals('/hans/showcase', $jsonDef->getRouterBase());
        $this->assertEquals(5, $jsonDef->getFixtureOrder());
        $this->assertFalse($jsonDef->isSubDocument());

        $this->assertEquals(
            '\Graviton\CoreBundle\Controller\ShowcaseExtensionController',
            $jsonDef->getBaseController()
        );

        $this->assertInstanceOf(
            'Graviton\GeneratorBundle\Definition\JsonDefinitionField',
            $jsonDef->getField('anotherInt')
        );
    }

    /**
     * minimal
     *
     * @return void
     */
    public function testMinimal()
    {
        $jsonDef = $this->loadJsonDefinition($this->minimalPath);
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinition', $jsonDef);

        $this->assertFalse($jsonDef->hasController());
        $this->assertFalse($jsonDef->hasFixtures());
        $this->assertFalse($jsonDef->getRouterBase());
        $this->assertTrue($jsonDef->isReadOnlyService());

        // test default behavior if nothing is specified..
        $this->assertEquals(0, count($jsonDef->getRelations()));
        $this->assertEquals(0, count($jsonDef->getFields()));
        $this->assertEquals(100, $jsonDef->getFixtureOrder());
        $this->assertEquals('RestController', $jsonDef->getBaseController());
        $this->assertNull($jsonDef->getField('test'));
        $this->assertNull($jsonDef->getNamespace());
        $this->assertFalse($jsonDef->isSubDocument());
    }

    /**
     * namespace
     *
     * @return void
     */
    public function testNamespaceSetting()
    {
        $jsonDef = $this->loadJsonDefinition($this->fullDefPath);

        $this->assertNull($jsonDef->getNamespace());
        $jsonDef->setNamespace('Hans\Namespace');
        $this->assertEquals('Hans\Namespace', $jsonDef->getNamespace());

        $jsonDef->setNamespace('Hans\Namespace\\');
        $this->assertEquals('Hans\Namespace', $jsonDef->getNamespace());
    }

    /**
     * sub document
     *
     * @return void
     */
    public function testSubDocument()
    {
        $jsonDef = $this->loadJsonDefinition($this->subDocumentPath);
        $this->assertTrue($jsonDef->isSubDocument());
    }

    /**
     * relations
     *
     * @return void
     */
    public function testRelations()
    {
        $jsonDef = $this->loadJsonDefinition($this->relationsPath);
        $relations = $jsonDef->getRelations();

        $this->assertEquals(2, count($relations));

        $this->assertInstanceOf(
            'Graviton\\GeneratorBundle\\Definition\\Schema\\Relation',
            $relations['anotherInt']
        );
        $this->assertEquals('embed', $relations['anotherInt']->getType());

        $this->assertInstanceOf(
            'Graviton\\GeneratorBundle\\Definition\\Schema\\Relation',
            $relations['someFloatyDouble']
        );
        $this->assertEquals('ref', $relations['someFloatyDouble']->getType());
    }

    /**
     * uri fixing
     *
     * @return void
     */
    public function testUriFixing()
    {
        $jsonDef = $this->loadJsonDefinition($this->wrongUriPath);
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinition', $jsonDef);
        $this->assertEquals('/hans/showcase', $jsonDef->getRouterBase());
    }

    /**
     * role set definition
     *
     * @return void
     */
    public function testRoles()
    {
        $jsonDef = $this->loadJsonDefinition($this->rolesPath);
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinition', $jsonDef);
        $this->assertEquals(array('GRAVITON_USER'), $jsonDef->getRoles());
    }

    /**
     * @return void
     */
    public function testNestedFields()
    {
        $definition = $this->loadJsonDefinition($this->nestedFieldPath);
        $this->assertEquals(
            [
                'id' => new JsonDefinitionField(
                    'id',
                    (new Schema\Field())
                        ->setName('id')
                        ->setType('varchar')
                ),
                'hash' => new JsonDefinitionHash(
                    'hash',
                    $definition,
                    [
                        'abc' => new JsonDefinitionField(
                            'abc',
                            (new Schema\Field())
                                ->setName('hash.abc')
                                ->setType('integer')
                        ),
                        'def' => new JsonDefinitionField(
                            'def',
                            (new Schema\Field())
                                ->setName('hash.def')
                                ->setType('boolean')
                        ),
                    ],
                    (new Schema\Field())
                        ->setName('hash')
                        ->setType('hash')
                        ->setExposeAs('$hash')
                        ->setRequired(true)
                ),
                'array' => new JsonDefinitionArray(
                    'array',
                    new JsonDefinitionField(
                        'array',
                        (new Schema\Field())
                            ->setName('array.0')
                            ->setType('string')
                    )
                ),
                'arrayarray' => new JsonDefinitionArray(
                    'arrayarray',
                    new JsonDefinitionArray(
                        'arrayarray',
                        new JsonDefinitionArray(
                            'arrayarray',
                            new JsonDefinitionField(
                                'arrayarray',
                                (new Schema\Field())
                                    ->setName('arrayarray.0.0.0')
                                    ->setType('integer')
                            )
                        )
                    )
                ),
                'arrayhash' => new JsonDefinitionArray(
                    'arrayhash',
                    new JsonDefinitionHash(
                        'arrayhash',
                        $definition,
                        [
                            'mno' => new JsonDefinitionField(
                                'mno',
                                (new Schema\Field())
                                    ->setName('arrayhash.0.mno')
                                    ->setType('string')
                            ),
                            'pqr' => new JsonDefinitionField(
                                'pqr',
                                (new Schema\Field())
                                    ->setName('arrayhash.0.pqr')
                                    ->setType('float')
                            ),
                        ]
                    )
                ),
                'deep' => new JsonDefinitionArray(
                    'deep',
                    new JsonDefinitionHash(
                        'deep',
                        $definition,
                        [
                            'b' => new JsonDefinitionArray(
                                'b',
                                new JsonDefinitionHash(
                                    'b',
                                    $definition,
                                    [
                                        'c' => new JsonDefinitionHash(
                                            'c',
                                            $definition,
                                            [
                                                'd' => new JsonDefinitionHash(
                                                    'd',
                                                    $definition,
                                                    [
                                                        'e' => new JsonDefinitionField(
                                                            'e',
                                                            (new Schema\Field())
                                                                ->setName('deep.0.b.0.c.d.e')
                                                                ->setType('varchar')
                                                        ),
                                                    ]
                                                ),
                                            ],
                                            (new Schema\Field())
                                                ->setName('deep.0.b.0.c')
                                                ->setType('hash')
                                                ->setDescription('description')
                                                ->setExposeAs('$c')
                                                ->setRequired(false)
                                        ),
                                    ]
                                )
                            ),
                            'c' => new JsonDefinitionField(
                                'c',
                                (new Schema\Field())
                                    ->setName('deep.0.c')
                                    ->setType('string')
                            ),
                            'd' => new JsonDefinitionArray(
                                'd',
                                new JsonDefinitionField(
                                    'd',
                                    (new Schema\Field())
                                        ->setName('deep.0.d.0')
                                        ->setType('integer')
                                )
                            ),
                        ]
                    )
                ),
            ],
            $definition->getFields()
        );
    }

    /**
     * @return void
     */
    public function testHashToJsonDefinition()
    {
        $definition = $this->loadJsonDefinition($this->nestedFieldPath);

        /** @var JsonDefinitionHash $field */
        $field = $this->getFieldByPath($definition, 'hash');
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinitionHash', $field);

        $this->assertEquals(
            (new JsonDefinition(
                (new Schema\Definition())
                    ->setId('FieldTestHash')
                    ->setIsSubDocument(true)
                    ->setTarget(
                        (new Schema\Target())
                            ->addField(
                                (new Schema\Field())
                                    ->setName('abc')
                                    ->setType('integer')
                            )
                            ->addField(
                                (new Schema\Field())
                                    ->setName('def')
                                    ->setType('boolean')
                            )
                    )
            )),
            $field->getJsonDefinition()
        );

        /** @var JsonDefinitionHash $field */
        $field = $this->getFieldByPath($definition, 'arrayhash.0');
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinitionHash', $field);

        $this->assertEquals(
            (new JsonDefinition(
                (new Schema\Definition())
                    ->setId('FieldTestArrayhash')
                    ->setIsSubDocument(true)
                    ->setTarget(
                        (new Schema\Target())
                            ->addField(
                                (new Schema\Field())
                                    ->setName('mno')
                                    ->setType('string')
                            )
                            ->addField(
                                (new Schema\Field())
                                    ->setName('pqr')
                                    ->setType('float')
                            )
                    )
            )),
            $field->getJsonDefinition()
        );

        /** @var JsonDefinitionHash $field */
        $field = $this->getFieldByPath($definition, 'deep.0');
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinitionHash', $field);

        $this->assertEquals(
            new JsonDefinition(
                (new Schema\Definition())
                    ->setId('FieldTestDeep')
                    ->setIsSubDocument(true)
                    ->setTarget(
                        (new Schema\Target())
                            ->addField(
                                (new Schema\Field())
                                    ->setName('b.0.c')
                                    ->setType('hash')
                                    ->setExposeAs('$c')
                                    ->setDescription('description')
                                    ->setRequired(false)
                            )
                            ->addField(
                                (new Schema\Field())
                                    ->setName('b.0.c.d.e')
                                    ->setType('varchar')
                            )
                            ->addField(
                                (new Schema\Field())
                                    ->setName('c')
                                    ->setType('string')
                            )
                            ->addField(
                                (new Schema\Field())
                                    ->setName('d.0')
                                    ->setType('integer')
                            )
                    )
            ),
            $field->getJsonDefinition()
        );
    }

    /**
     * @return void
     */
    public function testNestedRelations()
    {
        $definition = $this->loadJsonDefinition($this->nestedRelationsPath);

        /** @var JsonDefinitionHash $field */
        $field = $this->getFieldByPath($definition, 'hash');
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinitionHash', $field);

        $this->assertEquals(
            (new JsonDefinition(
                (new Schema\Definition())
                    ->setId('RelationTestHash')
                    ->setIsSubDocument(true)
                    ->setTarget(
                        (new Schema\Target())
                            ->addField(
                                (new Schema\Field())
                                    ->setName('embedOne')
                                    ->setType('class:Entity')
                            )
                            ->addField(
                                (new Schema\Field())
                                    ->setName('referenceOne')
                                    ->setType('class:Entity')
                            )
                            ->addField(
                                (new Schema\Field())
                                    ->setName('embedMany')
                                    ->setType('class:Entity[]')
                            )
                            ->addField(
                                (new Schema\Field())
                                    ->setName('referenceMany')
                                    ->setType('class:Entity[]')
                            )
                            ->addRelation(
                                (new Schema\Relation())
                                    ->setType(JsonDefinitionRel::REL_TYPE_EMBED)
                                    ->setLocalProperty('embedOne')
                            )
                            ->addRelation(
                                (new Schema\Relation())
                                    ->setType(JsonDefinitionRel::REL_TYPE_EMBED)
                                    ->setLocalProperty('embedMany')
                            )
                    )
            )),
            $field->getJsonDefinition()
        );

        /** @var JsonDefinitionRel $embedField */
        $embedField = $this->getFieldByPath($definition, 'hash.embedOne');
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinitionRel', $embedField);
        $this->assertEquals(JsonDefinitionRel::REL_TYPE_EMBED, $embedField->getDefAsArray()['relType']);
        $this->assertEquals('Entity', $embedField->getType());

        /** @var JsonDefinitionRel $embedField */
        $embedField = $this->getFieldByPath($definition, 'hash.embedMany.0');
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinitionRel', $embedField);
        $this->assertEquals(JsonDefinitionRel::REL_TYPE_EMBED, $embedField->getDefAsArray()['relType']);
        $this->assertEquals('Entity', $embedField->getType());

        /** @var JsonDefinitionRel $referenceField */
        $referenceField = $this->getFieldByPath($definition, 'hash.referenceOne');
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinitionRel', $referenceField);
        $this->assertEquals(JsonDefinitionRel::REL_TYPE_REF, $referenceField->getDefAsArray()['relType']);
        $this->assertEquals('Entity', $referenceField->getType());

        /** @var JsonDefinitionRel $referenceField */
        $referenceField = $this->getFieldByPath($definition, 'hash.referenceMany.0');
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinitionRel', $referenceField);
        $this->assertEquals(JsonDefinitionRel::REL_TYPE_REF, $referenceField->getDefAsArray()['relType']);
        $this->assertEquals('Entity', $referenceField->getType());



        /** @var JsonDefinitionRel $embedField */
        $embedField = $this->getFieldByPath($definition, 'deep.0.sub.embedOne');
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinitionRel', $embedField);
        $this->assertEquals(JsonDefinitionField::REL_TYPE_EMBED, $embedField->getDefAsArray()['relType']);
        $this->assertEquals('Entity', $embedField->getType());

        /** @var JsonDefinitionRel $embedField */
        $embedField = $this->getFieldByPath($definition, 'deep.0.sub.subsub.0.embedMany.0');
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinitionRel', $embedField);
        $this->assertEquals(JsonDefinitionField::REL_TYPE_EMBED, $embedField->getDefAsArray()['relType']);
        $this->assertEquals('Entity', $embedField->getType());

        /** @var JsonDefinitionRel $referenceField */
        $referenceField = $this->getFieldByPath($definition, 'deep.0.sub.referenceOne');
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinitionRel', $referenceField);
        $this->assertEquals(JsonDefinitionField::REL_TYPE_REF, $referenceField->getDefAsArray()['relType']);
        $this->assertEquals('Entity', $referenceField->getType());

        /** @var JsonDefinitionRel $referenceField */
        $referenceField = $this->getFieldByPath($definition, 'deep.0.sub.subsub.0.referenceMany.0');
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinitionRel', $referenceField);
        $this->assertEquals(JsonDefinitionField::REL_TYPE_REF, $referenceField->getDefAsArray()['relType']);
        $this->assertEquals('Entity', $referenceField->getType());
    }


    /**
     * Primitive array test
     *
     * @return void
     */
    public function testPrimitiveArray()
    {
        $definition = $this->loadJsonDefinition(__DIR__.'/resources/test-primitive-array.json');
        $this->assertEquals(
            (new JsonDefinition(
                (new Schema\Definition())
                    ->setId('PrimitiveArray')
                    ->setTarget(
                        (new Schema\Target())
                            ->addField(
                                (new Schema\Field())
                                    ->setName('id')
                                    ->setType('string')
                            )
                            ->addField(
                                (new Schema\Field())
                                    ->setName('intarray.0')
                                    ->setType('int')
                            )
                            ->addField(
                                (new Schema\Field())
                                    ->setName('hash.intarray.0')
                                    ->setType('int')
                            )
                            ->addField(
                                (new Schema\Field())
                                    ->setName('hasharray.0.intarray.0')
                                    ->setType('int')
                            )
                    )
            )),
            $definition
        );

        /** @var JsonDefinitionArray $field */
        $field = $this->getFieldByPath($definition, 'intarray');
        $this->assertInstanceOf(JsonDefinitionArray::class, $field);
        $this->assertEquals('int[]', $field->getType());
        $this->assertEquals('intarray', $field->getName());

        /** @var JsonDefinitionField $field */
        $field = $this->getFieldByPath($definition, 'intarray.0');
        $this->assertInstanceOf(JsonDefinitionField::class, $field);
        $this->assertEquals('int', $field->getType());
        $this->assertEquals('intarray', $field->getName());

        /** @var JsonDefinitionArray $field */
        $field = $this->getFieldByPath($definition, 'hash.intarray');
        $this->assertInstanceOf(JsonDefinitionArray::class, $field);
        $this->assertEquals('int[]', $field->getType());
        $this->assertEquals('intarray', $field->getName());

        /** @var JsonDefinitionField $field */
        $field = $this->getFieldByPath($definition, 'hash.intarray.0');
        $this->assertInstanceOf(JsonDefinitionField::class, $field);
        $this->assertEquals('int', $field->getType());
        $this->assertEquals('intarray', $field->getName());

        /** @var JsonDefinitionArray $field */
        $field = $this->getFieldByPath($definition, 'hasharray.0.intarray');
        $this->assertInstanceOf(JsonDefinitionArray::class, $field);
        $this->assertEquals('int[]', $field->getType());
        $this->assertEquals('intarray', $field->getName());

        /** @var JsonDefinitionField $field */
        $field = $this->getFieldByPath($definition, 'hasharray.0.intarray.0');
        $this->assertInstanceOf(JsonDefinitionField::class, $field);
        $this->assertEquals('int', $field->getType());
        $this->assertEquals('intarray', $field->getName());
    }

    /**
     * test if indexes are exposed in def
     *
     * @return void
     */
    public function testIndexes()
    {
        $jsonDef = $this->loadJsonDefinition($this->fullDefPath);
        $this->assertInternalType('array', $jsonDef->getIndexes());
    }

    /**
     * test if indexes are exposed in def
     *
     * @return void
     */
    public function testTextIndexes()
    {
        $jsonDef = $this->loadJsonDefinition($this->fullDefPath);
        $this->assertInternalType('array', $jsonDef->getTextIndexes());
    }

    /**
     * Get field by path
     *
     * @param JsonDefinition $definition JSON definition
     * @param string         $path       Path to field
     * @return DefinitionElementInterface
     */
    private function getFieldByPath(JsonDefinition $definition, $path)
    {
        $items = explode('.', $path);
        $field = $definition->getField(array_shift($items));
        foreach ($items as $item) {
            if ($item === '0') {
                if (!$field instanceof JsonDefinitionArray) {
                    throw new \InvalidArgumentException(sprintf('Error path: "%s"', $path));
                }
                $field = $field->getElement();
            } else {
                if (!$field instanceof JsonDefinitionHash) {
                    throw new \InvalidArgumentException(sprintf('Error path: "%s"', $path));
                }
                $field = $field->getJsonDefinition()->getField($item);
            }
        }
        return $field;
    }
}
