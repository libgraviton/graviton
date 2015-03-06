<?php
/**
 * test json definition
 */

namespace Graviton\GeneratorBundle\Tests\Definition;

use Graviton\GeneratorBundle\Definition\JsonDefinition;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DefinitionTest extends \PHPUnit_Framework_TestCase
{

    private $fullDefPath;
    private $minimalPath;
    private $noIdPath;
    private $invalidPath;
    private $wrongUriPath;
    private $subDocumentPath;
    private $relationsPath;

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
    }

    /**
     * invalid handling
     *
     * @expectedException \RuntimeException
     *
     * @return void
     */
    public function testInvalidHandling()
    {
        $jsonDef = new JsonDefinition($this->invalidPath);
    }
    /**
     * inexistent
     *
     * @expectedException \Exception
     *
     * @return void
     */
    public function testInexistentFile()
    {
        $jsonDef = new JsonDefinition($this->invalidPath.'suffix');
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
        $jsonDef = new JsonDefinition($this->noIdPath);
        $id = $jsonDef->getId();
    }

    /**
     * basics
     *
     * @return void
     */
    public function testBasics()
    {
        $jsonDef = new JsonDefinition($this->fullDefPath);
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinition', $jsonDef);
        $this->assertEquals('Showcase', $jsonDef->getId());
        $this->assertEquals($this->fullDefPath, $jsonDef->getFilename());
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
        $jsonDef = new JsonDefinition($this->fullDefPath);
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
        $jsonDef = new JsonDefinition($this->minimalPath);
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
        $jsonDef = new JsonDefinition($this->fullDefPath);

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
        $jsonDef = new JsonDefinition($this->subDocumentPath);
        $this->assertTrue($jsonDef->isSubDocument());
    }

    /**
     * dot notation
     *
     * @return void
     */
    public function testObjectNotationHandling()
    {
        $jsonDef = new JsonDefinition($this->fullDefPath);
        $fields = $jsonDef->getFields();

        // array (x.[0-9].y)
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinitionHash', $fields['nestedArray']);
        $this->assertTrue($fields['nestedArray']->isArrayHash());
        $this->assertEquals($jsonDef, $fields['nestedArray']->getParent());
        $this->assertEquals('hash', $fields['nestedArray']->getType());
        $this->assertEquals('\Document\ShowcaseNestedArray[]', $fields['nestedArray']->getTypeDoctrine());
        $this->assertEquals('array<\Document\ShowcaseNestedArray>', $fields['nestedArray']->getTypeSerializer());

        // object (x.y)
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinitionHash', $fields['contactCode']);
        $this->assertTrue($fields['contactCode']->isHash());
        $this->assertFalse($fields['contactCode']->isArrayHash());
        $this->assertEquals('hash', $fields['contactCode']->getType());
        $this->assertEquals('\Document\ShowcaseContactCode', $fields['contactCode']->getTypeDoctrine());
        $this->assertEquals('\Document\ShowcaseContactCode', $fields['contactCode']->getTypeSerializer());
    }

    /**
     * relations
     *
     * @return void
     */
    public function testRelations()
    {
        $jsonDef = new JsonDefinition($this->relationsPath);
        $relations = $jsonDef->getRelations();

        $this->assertEquals(2, count($relations));

        $this->assertInstanceOf('stdClass', $relations['anotherInt']);
        $this->assertEquals('embed', $relations['anotherInt']->type);
        $field = $jsonDef->getField('anotherInt');
        $this->assertEquals($field::REL_TYPE_EMBED, $field->getRelType());

        $this->assertInstanceOf('stdClass', $relations['someFloatyDouble']);
        $this->assertEquals('ref', $relations['someFloatyDouble']->type);
        $field = $jsonDef->getField('someFloatyDouble');
        $this->assertEquals($field::REL_TYPE_REF, $field->getRelType());
    }

    /**
     * uri fixing
     *
     * @return void
     */
    public function testUriFixing()
    {
        $jsonDef = new JsonDefinition($this->wrongUriPath);
        $this->assertInstanceOf('Graviton\GeneratorBundle\Definition\JsonDefinition', $jsonDef);
        $this->assertEquals('/hans/showcase', $jsonDef->getRouterBase());
    }
}
