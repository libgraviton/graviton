<?php
namespace Graviton\GeneratorBundle\Tests\Definition\Strategy;

use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Graviton\GeneratorBundle\Definition\Schema\Definition;
use Graviton\GeneratorBundle\Definition\Loader\Strategy\JsonStrategy;

/**
 */
class JsonStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testLoad()
    {
        $json = file_get_contents(__DIR__.'/test.json');

        $serializer = $this
            ->getMockBuilder('Jms\\Serializer\\SerializerInterface')
            ->disableOriginalConstructor()
            ->setMethods(['serialize', 'deserialize'])
            ->getMock();
        $serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with(
                $json,
                'Graviton\\GeneratorBundle\\Definition\\Schema\\Definition',
                'json'
            )
            ->will(
                $this->returnValue((new Definition())->setId('a'))
            );

        $strategy = new JsonStrategy($serializer);
        $data = $strategy->load($json);

        $this->assertContainsOnlyInstancesOf('Graviton\\GeneratorBundle\\Definition\\JsonDefinition', $data);
        $this->assertEquals(
            $data,
            [new JsonDefinition((new Definition())->setId('a'))]
        );
    }

    /**
     * @param string $input
     * @param bool $result
     * @return void
     *
     * @dataProvider dataSupports()
     */
    public function testSupports($input, $result)
    {
        $serializer = $this
            ->getMockBuilder('Jms\\Serializer\\SerializerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $strategy = new JsonStrategy($serializer);
        $this->assertSame($result, $strategy->supports($input));
    }

    /**
     * @return array
     * @see testSupports()
     */
    public function dataSupports()
    {
        return [
            [
                123,
                false,
            ],
            [
                true,
                false,
            ],
            [
                [],
                false,
            ],
            [
                (object)['a' => 'a'],
                false,
            ],
            [
                '',
                false,
            ],
            [
                '[{"a":"a"}]',
                false,
            ],
            [
                '{"a":"a"}',
                true,
            ],
        ];
    }
}
