<?php
/**
 * DateArrayTypeTest class file
 */

namespace Graviton\Tests\Rest\Types;

use Doctrine\ODM\MongoDB\Types\Type;
use Graviton\DocumentBundle\Types\DateArrayType;
use MongoDB\BSON\UTCDateTime;

/**
 * DateArrayType test
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class DateArrayTypeTest extends BaseDoctrineTypeTestCase
{
    /**
     * @var DateArrayType
     */
    private $type;

    /**
     * setup type we want to test
     *
     * @return void
     */
    public function setUp() : void
    {
        Type::registerType('datearray', DateArrayType::class);
        $this->type = Type::getType('datearray');
    }

    /**
     * Test DateArrayType::convertToDatabaseValue()
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
            $this->type->convertToDatabaseValue(['not a date'])
        );
        $this->assertEquals(
            [],
            $this->type->convertToDatabaseValue([(object) []])
        );
        $this->assertEquals(
            [new UTCDateTime(\DateTime::createFromFormat(\DateTimeInterface::ISO8601, '2015-10-02T15:04:00+06:00'))],
            $this->type->convertToDatabaseValue(['2015-10-02T15:04:00+06:00'])
        );
    }

    /**
     * Test DateArrayType::closureToMongo()
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
            ['not a date'],
            $this->type->closureToMongo()
        );
        $this->assertEqualsClosure(
            [],
            [(object) []],
            $this->type->closureToMongo()
        );
        $this->assertEqualsClosure(
            [new UTCDateTime(\DateTime::createFromFormat(\DateTimeInterface::ISO8601, '2015-10-02T15:04:00+06:00'))],
            ['2015-10-02T15:04:00+06:00'],
            $this->type->closureToMongo()
        );
    }

    /**
     * Test DateArrayType::convertToPHPValue()
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
            $this->type->convertToPHPValue(['not a date'])
        );
        $this->assertEquals(
            [],
            $this->type->convertToPHPValue([(object) []])
        );
        $this->assertEquals(
            [\DateTime::createFromFormat(\DateTimeInterface::ISO8601, '2015-10-02T15:04:00+06:00')],
            $this->type->convertToPHPValue(['2015-10-02T15:04:00+06:00'])
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
            ['not a date'],
            $this->type->closureToPHP()
        );
        $this->assertEqualsClosure(
            [],
            [(object) []],
            $this->type->closureToPHP()
        );
        $this->assertEqualsClosure(
            [new \DateTime('2015-10-02T15:04:00+06:00')],
            ['2015-10-02T15:04:00+06:00'],
            $this->type->closureToPHP()
        );
    }
}
