<?php
/**
 * test for constraint builder calling
 */

namespace Graviton\GeneratorBundle\Tests\SchemaBuilder;

use Graviton\GeneratorBundle\Schema\SchemaBuilder;
use Graviton\GeneratorBundle\Tests\SchemaBuilder\Builder\DummyBuilderA;
use PHPUnit\Framework\TestCase;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SchemaBuilderTest extends TestCase
{

    /**
     * test the builder handling
     *
     * @return void
     */
    public function testBuilderHandling()
    {
        $sut = new SchemaBuilder();
        $dummyBuilder = new DummyBuilderA();
        $sut->addSchemaBuilder($dummyBuilder);

        $changedProperty = $sut->buildSchema([], [], []);

        $this->assertEquals('THIS WAS SET BY DUMMY-A', $changedProperty['title']);
    }
}
