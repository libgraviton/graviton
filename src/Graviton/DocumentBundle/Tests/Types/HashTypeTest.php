<?php
/**
 * HashTypeTest class file
 */
namespace Graviton\DocumentBundle\Tests\Types;

use Doctrine\ODM\MongoDB\Types\Type;
use Graviton\DocumentBundle\Entity\Hash;
use Graviton\DocumentBundle\Types\HashType;

/**
 * HashType test
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class HashTypeTest extends BaseDoctrineTypeTestCase
{
    /**
     * @var HashType
     */
    private $type;

    /**
     * setup type we want to test
     *
     * @return void
     */
    public function setUp()
    {
        Type::registerType('hash', 'Graviton\DocumentBundle\Types\HashType');
        $this->type = Type::getType('hash');
    }

    /**
     * Test HashType::convertToDatabaseValue()
     *
     * @return void
     */
    public function testConvertToDatabaseValue()
    {
        $this->assertEquals(
            null,
            $this->type->convertToDatabaseValue(null)
        );
        $this->assertEquals(
            null,
            $this->type->convertToDatabaseValue('')
        );
        $this->assertEquals(
            (object) [],
            $this->type->convertToDatabaseValue([])
        );
        $this->assertEquals(
            (object) ['a'],
            $this->type->convertToDatabaseValue(['a'])
        );
        $this->assertEquals(
            (object) [],
            $this->type->convertToDatabaseValue(new \ArrayObject())
        );
        $this->assertEquals(
            (object) ['a' => 'b'],
            $this->type->convertToDatabaseValue(new \ArrayObject(['a' => 'b']))
        );
    }

    /**
     * Test HashType::closureToMongo()
     *
     * @return void
     */
    public function testClosureToMongo()
    {
        $this->assertEqualsClosure(
            null,
            null,
            $this->type->closureToMongo()
        );
        $this->assertEqualsClosure(
            null,
            '',
            $this->type->closureToMongo()
        );
        $this->assertEqualsClosure(
            (object) [],
            [],
            $this->type->closureToMongo()
        );
        $this->assertEqualsClosure(
            (object) ['a'],
            ['a'],
            $this->type->closureToMongo()
        );
        $this->assertEqualsClosure(
            (object) [],
            new \ArrayObject(),
            $this->type->closureToMongo()
        );
        $this->assertEqualsClosure(
            (object) ['a' => 'b'],
            new \ArrayObject(['a' => 'b']),
            $this->type->closureToMongo()
        );
    }

    /**
     * Test HashType::convertToPHPValue()
     *
     * @return void
     */
    public function testConvertToPHPValue()
    {
        $this->assertEquals(
            null,
            $this->type->convertToPHPValue(null)
        );
        $this->assertEquals(
            null,
            $this->type->convertToPHPValue('')
        );
        $this->assertEquals(
            new Hash([]),
            $this->type->convertToPHPValue([])
        );
        $this->assertEquals(
            new Hash(['a']),
            $this->type->convertToPHPValue(['a'])
        );
        $this->assertEquals(
            new Hash(['a' => 'b']),
            $this->type->convertToPHPValue(['a' => 'b'])
        );
    }

    /**
     * Test HashType::closureToPHP()
     *
     * @return void
     */
    public function testClosureToPHP()
    {
        $this->assertEqualsClosure(
            null,
            null,
            $this->type->closureToPHP()
        );
        $this->assertEqualsClosure(
            null,
            '',
            $this->type->closureToPHP()
        );
        $this->assertEqualsClosure(
            new Hash([]),
            [],
            $this->type->closureToPHP()
        );
        $this->assertEqualsClosure(
            new Hash(['a']),
            ['a'],
            $this->type->closureToPHP()
        );
        $this->assertEqualsClosure(
            new Hash(['a' => 'b']),
            ['a' => 'b'],
            $this->type->closureToPHP()
        );
    }
}
