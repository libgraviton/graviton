<?php
/**
 * test json definition
 */

namespace Graviton\GeneratorBundle\Tests\Definition\Strategy;

use Graviton\GeneratorBundle\Definition\Loader\Strategy\ScanStrategy;
use Graviton\GeneratorBundle\Definition\JsonDefinition;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DefinitionTest extends \PHPUnit_Framework_TestCase
{

    private $fullDefPath;

    public function setUp()
    {
        $this->fullDefPath = __DIR__.'/resources/test-full.json';
        $this->minimalPath = __DIR__.'/resources/test-minimal.json';
    }

    /**
     * check loading with null input
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
}
