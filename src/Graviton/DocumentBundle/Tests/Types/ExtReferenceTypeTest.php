<?php
/**
 * verify extref custom type
 */

namespace Graviton\DocumentBundle\Tests\Types;

use Graviton\DocumentBundle\Service\ExtReferenceConverterInterface;
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
     * @var ExtReferenceConverterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $converter;
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

        $this->converter = $this->getMockBuilder(ExtReferenceConverterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDbRef', 'getUrl'])
            ->getMock();
    }

    /**
     * @expectedException \RuntimeException
     *
     * @return void
     */
    public function testMongoRefFromValueWithException()
    {
        $url = __FILE__;

        $this->converter
            ->expects($this->once())
            ->method('getDbRef')
            ->with($url)
            ->willThrowException(new \InvalidArgumentException);

        $this->type->setConverter($this->converter);
        $this->type->convertToDatabaseValue($url);
    }

    /**
     * verify that we get a mongodbref
     *
     * @return void
     */
    public function testMongoRefFromValue()
    {
        $url = __FILE__;
        $dbRef = (object) \MongoDBRef::create(__METHOD__, __FILE__);

        $this->converter
            ->expects($this->once())
            ->method('getDbRef')
            ->with($url)
            ->willReturn($dbRef);

        $this->type->setConverter($this->converter);
        $this->assertEquals($dbRef, $this->type->convertToDatabaseValue($url));
    }

    /**
     * Test ConvertToPHPValue
     *
     * @return void
     */
    public function testConvertToPHPValueWithException()
    {
        $dbRef = (object) \MongoDBRef::create(__METHOD__, __FILE__);

        $this->converter
            ->expects($this->once())
            ->method('getUrl')
            ->with($dbRef)
            ->willThrowException(new \InvalidArgumentException);

        $this->type->setConverter($this->converter);
        $this->assertEquals('', $this->type->convertToPHPValue($dbRef));
    }

    /**
     * Test ConvertToPHPValue
     *
     * @return void
     */
    public function testConvertToPHPValue()
    {
        $dbRef = (object) \MongoDBRef::create(__METHOD__, __FILE__);

        $this->converter
            ->expects($this->once())
            ->method('getUrl')
            ->with($dbRef)
            ->willReturn(__FILE__);

        $this->type->setConverter($this->converter);
        $this->assertEquals(__FILE__, $this->type->convertToPHPValue($dbRef));
    }

    /**
     * Test ExtReference::closureToPHP()
     *
     * @return void
     */
    public function testClosureToPHP()
    {
        $this->assertEqualsClosure(
            'null',
            null,
            $this->type->closureToPHP()
        );
        $this->assertEqualsClosure(
            '{}',
            (object) [],
            $this->type->closureToPHP()
        );
        $this->assertEqualsClosure(
            '{"$ref":"A","$id":"b"}',
            (object) ['$ref' => 'A', '$id' => 'b'],
            $this->type->closureToPHP()
        );
    }
}
