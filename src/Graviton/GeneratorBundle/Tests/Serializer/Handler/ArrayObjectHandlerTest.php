<?php
/**
 * ArrayObjectHandlerTest class file
 */

namespace Graviton\GeneratorBundle\Tests\Serializer\Handler;

use Graviton\GeneratorBundle\Serializer\Handler\ArrayObjectHandler;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;

/**
 * Test ArrayObjectHandler
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ArrayObjectHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test ArrayObjectHandler::serializeArrayObjectToJson()
     *
     * @return void
     */
    public function testSerializeArrayObjectToJson()
    {
        $serialized = ['a' => __LINE__, 'b' => __LINE__];
        $deserialized = new \ArrayObject(['c' => __LINE__, 'd' => __LINE__]);

        $type = [__METHOD__];
        $context = SerializationContext::create();

        $serializationVisitor = $this->getMockBuilder('JMS\Serializer\JsonSerializationVisitor')
            ->disableOriginalConstructor()
            ->setMethods(['visitArray'])
            ->getMock();
        $serializationVisitor
            ->expects($this->once())
            ->method('visitArray')
            ->with($deserialized->getArrayCopy(), $type, $context)
            ->willReturn($serialized);

        $this->assertEquals(
            new \ArrayObject($serialized),
            (new ArrayObjectHandler())->serializeArrayObjectToJson(
                $serializationVisitor,
                $deserialized,
                $type,
                $context
            )
        );
    }

    /**
     * Test ArrayObjectHandler::deserializeArrayObjectFromJson()
     *
     * @return void
     */
    public function testDeserializeArrayObjectFromJson()
    {
        $serialized = ['a' => __LINE__, 'b' => __LINE__];
        $deserialized = new \ArrayObject(['c' => __LINE__, 'd' => __LINE__]);

        $type = [__METHOD__];
        $context = DeserializationContext::create();

        $deserializationVisitor = $this->getMockBuilder('JMS\Serializer\JsonDeserializationVisitor')
            ->disableOriginalConstructor()
            ->setMethods(['visitArray'])
            ->getMock();
        $deserializationVisitor
            ->expects($this->once())
            ->method('visitArray')
            ->with($serialized, $type, $context)
            ->willReturn($deserialized->getArrayCopy());

        $this->assertEquals(
            $deserialized,
            (new ArrayObjectHandler())->deserializeArrayObjectFromJson(
                $deserializationVisitor,
                $serialized,
                $type,
                $context
            )
        );
    }

    /**
     * Test serializer
     *
     * @param string   $serialized   Serialized data
     * @param TestData $deserialized Deserialized data
     * @return void
     * @dataProvider dataSerializerTest
     */
    public function testSerializer($serialized, $deserialized)
    {
        $serializer = SerializerBuilder::create()
            ->addDefaultHandlers()
            ->addDefaultSerializationVisitors()
            ->addDefaultDeserializationVisitors()
            ->configureHandlers(
                function (HandlerRegistry $registry) {
                    $registry->registerHandler(
                        GraphNavigator::DIRECTION_SERIALIZATION,
                        'ArrayObject',
                        'json',
                        [new ArrayObjectHandler(), 'serializeArrayObjectToJson']
                    );
                    $registry->registerHandler(
                        GraphNavigator::DIRECTION_DESERIALIZATION,
                        'ArrayObject',
                        'json',
                        [new ArrayObjectHandler(), 'deserializeArrayObjectFromJson']
                    );
                }
            )
            ->addMetadataDir(
                __DIR__.'/resources/config/serializer',
                'Graviton\\GeneratorBundle\\Tests\\Serializer\\Handler'
            )
            ->setCacheDir(sys_get_temp_dir())
            ->setDebug(true)
            ->build();

        $this->assertEquals(
            $serialized,
            $serializer->serialize($deserialized, 'json')
        );
        $this->assertEquals(
            $deserialized,
            $serializer->deserialize($serialized, get_class($deserialized), 'json')
        );
    }

    /**
     * @return array
     */
    public function dataSerializerTest()
    {
        return [
            [
                '{"a":{"a":"a"},"b":[{"b":true,"c":1}]}',
                (new TestData())
                    ->setA(new \ArrayObject(['a' => 'a']))
                    ->setB([new \ArrayObject(['b' => true, 'c' => 1])]),
            ],
            [
                '{"a":{},"b":[{},{}]}',
                (new TestData())
                    ->setA(new \ArrayObject())
                    ->setB([new \ArrayObject(), new \ArrayObject()]),
            ],
        ];
    }
}
