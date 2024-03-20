<?php
/**
 * HashHandlerTest class file
 */

namespace Graviton\Tests\Rest\Serializer\Handler;

use Graviton\DocumentBundle\Entity\Hash;
use Graviton\DocumentBundle\Serializer\Handler\HashHandler;
use Graviton\DocumentBundle\Serializer\Visitor\JsonDeserializationVisitorFactory;
use Graviton\DocumentBundle\Serializer\Visitor\JsonSerializationVisitorFactory;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Test HashHandler
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class HashHandlerTest extends \PHPUnit\Framework\TestCase
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

        $serializationVisitor = (new JsonSerializationVisitorFactory())->getVisitor();

        $this->assertEquals(
            $hash,
            (new HashHandler(new RequestStack()))->serializeHashToJson(
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

        $deserializationVisitor = (new JsonDeserializationVisitorFactory())->getVisitor();

        $this->assertEquals(
            $hash,
            (new HashHandler(new RequestStack()))->deserializeHashFromJson(
                $deserializationVisitor,
                $array,
                $type,
                $context
            )
        );
    }
}
