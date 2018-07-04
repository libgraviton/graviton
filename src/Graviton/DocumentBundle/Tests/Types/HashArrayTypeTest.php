<?php
/**
 * HashArrayTypeTest class file
 */

namespace Graviton\DocumentBundle\Tests\Types;

use Doctrine\ODM\MongoDB\Types\Type;
use Graviton\DocumentBundle\Entity\Hash;
use Graviton\DocumentBundle\Types\HashArrayType;

/**
 * HashArrayType test
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class HashArrayTypeTest extends BaseDoctrineTypeTestCase
{
    /**
     * @var HashArrayType
     */
    private $type;

    /**
     * setup type we want to test
     *
     * @return void
     */
    public function setUp()
    {
        Type::registerType('hasharray', HashArrayType::class);
        $this->type = Type::getType('hasharray');
    }

    /**
     * Test HashArrayType::convertToDatabaseValue()
     *
     * @return void
     */
    public function testConvertToDatabaseValue()
    {
        $this->assertEquals(
            [],
            $this->type->convertToDatabaseValue(null)
        );
        $this->assertEquals(
            [],
            $this->type->convertToDatabaseValue('')
        );
        $this->assertEquals(
            [],
            $this->type->convertToDatabaseValue((object) [])
        );
        $this->assertEquals(
            [(object) []],
            $this->type->convertToDatabaseValue([new \ArrayObject()])
        );
        $this->assertEquals(
            [(object) ['a' => 'b']],
            $this->type->convertToDatabaseValue([new \ArrayObject(['a' => 'b'])])
        );
    }

    /**
     * Test HashArrayType::closureToMongo()
     *
     * @return void
     */
    public function testClosureToMongo()
    {
        $this->assertEqualsClosure(
            [],
            null,
            $this->type->closureToMongo()
        );
        $this->assertEqualsClosure(
            [],
            '',
            $this->type->closureToMongo()
        );
        $this->assertEqualsClosure(
            [],
            (object) [],
            $this->type->closureToMongo()
        );
        $this->assertEqualsClosure(
            [(object) []],
            [new \ArrayObject()],
            $this->type->closureToMongo()
        );
        $this->assertEqualsClosure(
            [(object) ['a' => 'b']],
            [new \ArrayObject(['a' => 'b'])],
            $this->type->closureToMongo()
        );
    }

    /**
     * Test HashArrayType::convertToPHPValue()
     *
     * @return void
     */
    public function testConvertToPHPValue()
    {
        $this->assertEquals(
            [],
            $this->type->convertToPHPValue(null)
        );
        $this->assertEquals(
            [],
            $this->type->convertToPHPValue('')
        );
        $this->assertEquals(
            [],
            $this->type->convertToPHPValue([])
        );
        $this->assertEquals(
            [],
            $this->type->convertToPHPValue(['a'])
        );
        $this->assertEquals(
            [new Hash(['a' => 'b'])],
            $this->type->convertToPHPValue([['a' => 'b']])
        );
        $this->assertEquals(
            [new Hash([])],
            $this->type->convertToPHPValue([[]])
        );
    }

    /**
     * Test HashArrayType::closureToPHP()
     *
     * @return void
     */
    public function testClosureToPHP()
    {
        $this->assertEqualsClosure(
            [],
            null,
            $this->type->closureToPHP()
        );
        $this->assertEqualsClosure(
            [],
            '',
            $this->type->closureToPHP()
        );
        $this->assertEqualsClosure(
            [],
            [],
            $this->type->closureToPHP()
        );
        $this->assertEqualsClosure(
            [],
            ['a'],
            $this->type->closureToPHP()
        );
        $this->assertEqualsClosure(
            [new Hash(['a' => 'b'])],
            [['a' => 'b']],
            $this->type->closureToPHP()
        );
        $this->assertEqualsClosure(
            [new Hash([])],
            [[]],
            $this->type->closureToPHP()
        );
    }
}
