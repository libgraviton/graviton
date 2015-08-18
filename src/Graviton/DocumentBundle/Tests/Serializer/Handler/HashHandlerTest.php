<?php
/**
 * HashHandlerTest class file
 */

namespace Graviton\DocumentBundle\Tests\Serializer\Handler;

use Graviton\DocumentBundle\Entity\Hash;
use Graviton\DocumentBundle\Serializer\Handler\HashHandler;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;

/**
 * Test HashHandler
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/GPL GPL
 * @link     http://swisscom.ch
 */
class HashHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test HashHandler::serializeHashToJson()
     *
     * @return void
     */
    public function testSerializeHashToJson()
    {
        $hash = new Hash([__METHOD__]);

        $type = [__FILE__];
        $context = SerializationContext::create();

        $serializationVisitor = $this->getMockBuilder('JMS\Serializer\JsonSerializationVisitor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals(
            $hash,
            (new HashHandler())->serializeHashToJson(
                $serializationVisitor,
                $hash,
                $type,
                $context
            )
        );
    }

    /**
     * Test HashHandler::deserializeHashFromJson()
     *
     * @return void
     */
    public function testDeserializeHashFromJson()
    {
        $array = [__METHOD__];
        $hash = new Hash($array);

        $type = [__FILE__];
        $context = DeserializationContext::create();

        $deserializationVisitor = $this->getMockBuilder('JMS\Serializer\JsonDeserializationVisitor')
            ->disableOriginalConstructor()
            ->setMethods(['visitArray'])
            ->getMock();
        $deserializationVisitor
            ->expects($this->once())
            ->method('visitArray')
            ->with($array, $type, $context)
            ->willReturn($array);

        $this->assertEquals(
            $hash,
            (new HashHandler())->deserializeHashFromJson(
                $deserializationVisitor,
                $array,
                $type,
                $context
            )
        );
    }
}
