<?php
/**
 * verify extref custom type
 */

namespace Graviton\DocumentBundle\Tests\Types;

use Graviton\DocumentBundle\Types\ExtReference;
use Graviton\DocumentBundle\Entity\ExtReference as ExtRef;
use Doctrine\ODM\MongoDB\Types\Type;

/**
 * Test ExtReference
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/GPL GPL
 * @link     http://swisscom.ch
 */
class ExtReferenceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExtReference
     */
    private $type;

    /**
     * setup type we want to test
     *
     * @return void
     */
    public function setUp()
    {
        Type::registerType('extref', ExtReference::class);
        $this->type = Type::getType('extref');
    }

    /**
     * Assert that expected result is equal to closure return value
     *
     * @param mixed  $expected      Expected value
     * @param mixed  $value         This value will be passed to closure
     * @param string $closureString Closure to eval
     * @return void
     */
    private function assertEqualsClosure($expected, $value, $closureString)
    {
        $return = null;
        eval($closureString);

        $this->assertEquals($expected, $return);
    }

    /**
     * Test ExtReference::convertToDatabaseValue()
     *
     * @return void
     */
    public function testConvertToDatabaseValue()
    {
        $this->assertEquals(
            \MongoDBRef::create(__METHOD__, __FILE__),
            $this->type->convertToDatabaseValue(ExtRef::create(__METHOD__, __FILE__))
        );
        $this->assertEquals(
            null,
            $this->type->convertToDatabaseValue(null)
        );
    }

    /**
     * Test ExtReference::closureToMongo()
     *
     * @return void
     */
    public function testClosureToMongo()
    {
        $this->assertEqualsClosure(
            \MongoDBRef::create(__METHOD__, __FILE__),
            ExtRef::create(__METHOD__, __FILE__),
            $this->type->closureToMongo()
        );
        $this->assertEqualsClosure(
            null,
            null,
            $this->type->closureToMongo()
        );
    }

    /**
     * Test ExtReference::convertToPHPValue()
     *
     * @return void
     */
    public function testConvertToPHPValue()
    {
        $this->assertEquals(
            ExtRef::create(__METHOD__, __FILE__),
            $this->type->convertToPHPValue(\MongoDBRef::create(__METHOD__, __FILE__))
        );
        $this->assertEquals(
            null,
            $this->type->convertToPHPValue(null)
        );
    }

    /**
     * Test ExtReference::closureToPHP()
     *
     * @return void
     */
    public function testClosureToPHP()
    {
        $this->assertEqualsClosure(
            ExtRef::create(__METHOD__, __FILE__),
            \MongoDBRef::create(__METHOD__, __FILE__),
            $this->type->closureToPHP()
        );
        $this->assertEqualsClosure(
            null,
            null,
            $this->type->closureToPHP()
        );
    }
}
