<?php
/**
 * verify extref custom type
 */

namespace Graviton\DocumentBundle\Tests\Types;

use Graviton\DocumentBundle\Entity\ExtReference;
use Graviton\DocumentBundle\Types\ExtReferenceType;
use Doctrine\ODM\MongoDB\Types\Type;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/GPL GPL
 * @link     http://swisscom.ch
 */
class ExtReferenceTypeTest extends BaseDoctrineTypeTestCase
{
    /**
     * @var ExtReferenceType
     */
    private $type;

    /**
     * setup type we want to test
     *
     * @return void
     */
    public function setUp()
    {
        Type::registerType('extref', ExtReferenceType::class);
        $this->type = Type::getType('extref');
    }

    /**
     * Test ExtReferenceType::convertToDatabaseValue()
     *
     * @return void
     */
    public function testConvertToDatabaseValue()
    {
        $this->assertEquals(
            \MongoDBRef::create(__METHOD__, __FILE__),
            $this->type->convertToDatabaseValue(ExtReference::create(__METHOD__, __FILE__))
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
            ExtReference::create(__METHOD__, __FILE__),
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
            ExtReference::create(__METHOD__, __FILE__),
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
            ExtReference::create(__METHOD__, __FILE__),
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
