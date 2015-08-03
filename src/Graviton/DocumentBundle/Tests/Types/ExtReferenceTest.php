<?php
/**
 * verify extref custom type
 */

namespace Graviton\DocumentBundle\Tests\Types;

use Graviton\DocumentBundle\Service\ExtReferenceResolverInterface;
use Graviton\DocumentBundle\Types\ExtReference;
use Doctrine\ODM\MongoDB\Types\Type;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/GPL GPL
 * @link     http://swisscom.ch
 */
class ExtReferenceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExtReferenceResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resolver;
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
        Type::registerType('extref', 'Graviton\DocumentBundle\Types\ExtReference');
        $this->type = Type::getType('extref');

        $this->resolver = $this->getMockBuilder('\Graviton\DocumentBundle\Service\ExtReferenceResolverInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getDbValue', 'getUrl'])
            ->getMock();
    }

    /**
     * @expectedException \RuntimeException
     *
     * @return void
     */
    public function testExceptWithoutResolver()
    {
        $this->type->convertToDatabaseValue('');
    }

    /**
     * @expectedException \RuntimeException
     *
     * @return void
     */
    public function testMongoRefFromValueWithException()
    {
        $url = __FILE__;

        $this->resolver
            ->expects($this->once())
            ->method('getDbValue')
            ->with($url)
            ->willThrowException( new \InvalidArgumentException);

        $this->type->setResolver($this->resolver);
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
        $dbRef = [
            '$ref' => __METHOD__,
            '$id' => __LINE__,
        ];

        $this->resolver
            ->expects($this->once())
            ->method('getDbValue')
            ->with($url)
            ->willReturn($dbRef);

        $this->type->setResolver($this->resolver);
        $this->assertEquals($dbRef, $this->type->convertToDatabaseValue($url));
    }

    /**
     * Test ConvertToPHPValue
     *
     * @return void
     */
    public function testConvertToPHPValueWithException()
    {
        $dbRef = [
            '$ref' => __METHOD__,
            '$id' => __LINE__,
        ];

        $this->resolver
            ->expects($this->once())
            ->method('getUrl')
            ->with($dbRef)
            ->willThrowException( new \InvalidArgumentException);

        $this->type->setResolver($this->resolver);
        $this->assertEquals('', $this->type->convertToPHPValue($dbRef));
    }

    /**
     * Test ConvertToPHPValue
     *
     * @return void
     */
    public function testConvertToPHPValue()
    {
        $dbRef = [
            '$ref' => __METHOD__,
            '$id' => __LINE__,
        ];

        $this->resolver
            ->expects($this->once())
            ->method('getUrl')
            ->with($dbRef)
            ->willReturn(__FILE__);

        $this->type->setResolver($this->resolver);
        $this->assertEquals(__FILE__, $this->type->convertToPHPValue($dbRef));
    }
}
