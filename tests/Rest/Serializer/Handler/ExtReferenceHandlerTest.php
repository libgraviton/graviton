<?php
/**
 * ExtReferenceHandlerTest class file
 */

namespace Graviton\Tests\Rest\Serializer\Handler;

use Graviton\DocumentBundle\Entity\ExtReference;
use Graviton\DocumentBundle\Serializer\Handler\ExtReferenceHandler;
use Graviton\DocumentBundle\Serializer\Visitor\JsonDeserializationVisitor;
use Graviton\DocumentBundle\Serializer\Visitor\JsonDeserializationVisitorFactory;
use Graviton\DocumentBundle\Serializer\Visitor\JsonSerializationVisitor;
use Graviton\DocumentBundle\Serializer\Visitor\JsonSerializationVisitorFactory;
use Graviton\DocumentBundle\Service\ExtReferenceConverter;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test ExtReferenceHandler
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ExtReferenceHandlerTest extends TestCase
{
    /**
     * @var ExtReferenceConverter|MockObject
     */
    private $converter;
    /**
     * @var JsonSerializationVisitor|MockObject
     */
    private $serializationVisitor;
    /**
     * @var JsonDeserializationVisitor|MockObject
     */
    private $deserializationVisitor;

    /**
     * setup type we want to test
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->converter = $this->getMockBuilder('\Graviton\DocumentBundle\Service\ExtReferenceConverter')
            ->disableOriginalConstructor()
            ->onlyMethods(['getExtReference', 'getUrl'])
            ->getMock();
        $this->serializationVisitor = (new JsonSerializationVisitorFactory())->getVisitor();
        $this->deserializationVisitor = (new JsonDeserializationVisitorFactory())->getVisitor();
    }

    /**
     * Test ExtReferenceHandler::serializeExtReferenceToJson() with error
     *
     * @return void
     */
    public function testSerializeExtReferenceToJsonWithError()
    {
        $type = [__LINE__];
        $context = SerializationContext::create();

        $extref = ExtReference::create(__METHOD__, __FILE__);

        $handler = new ExtReferenceHandler($this->converter);
        $this->assertEquals(
            null,
            $handler->serializeExtReferenceToJson(
                $this->serializationVisitor,
                $extref,
                $type,
                $context
            )
        );
    }

    /**
     * Test ExtReferenceHandler::serializeExtReferenceToJson()
     *
     * @return void
     */
    public function testSerializeExtReferenceToJson()
    {
        $type = [__LINE__];
        $context = SerializationContext::create();

        $url = __FUNCTION__;
        $extref = ExtReference::create(__METHOD__, __FILE__);

        $this->converter
            ->method("getUrl")
            ->with($extref)
            ->willReturn($url);

        $handler = new ExtReferenceHandler($this->converter);
        $this->assertEquals(
            $url,
            $handler->serializeExtReferenceToJson(
                $this->serializationVisitor,
                $extref,
                $type,
                $context
            )
        );
    }

    /**
     * Test ExtReferenceHandler::deserializeExtReferenceFromJson() with error
     *
     * @return void
     */
    public function testDeserializeExtReferenceFromJsonWithError()
    {
        $type = [__LINE__];
        $context = DeserializationContext::create();

        $url = __FUNCTION__;

        $handler = new ExtReferenceHandler($this->converter);
        $this->assertEquals(
            null,
            $handler->deserializeExtReferenceFromJson(
                $this->deserializationVisitor,
                $url,
                $type,
                $context
            )
        );
    }

    /**
     * Test ExtReferenceHandler::deserializeExtReferenceFromJson()
     *
     * @return void
     */
    public function testDeserializeExtReferenceFromJson()
    {
        $type = [__LINE__];
        $context = DeserializationContext::create();

        $url = __FUNCTION__;
        $extref = ExtReference::create(__METHOD__, __FILE__);

        $this->converter
            ->expects($this->once())
            ->method('getExtReference')
            ->with($url)
            ->willReturn($extref);

        $handler = new ExtReferenceHandler($this->converter);
        $this->assertEquals(
            $extref,
            $handler->deserializeExtReferenceFromJson(
                $this->deserializationVisitor,
                $url,
                $type,
                $context
            )
        );
    }
}
