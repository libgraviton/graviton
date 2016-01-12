<?php
namespace Graviton\GeneratorBundle\Tests\Definition\Strategy;

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
        $json = file_get_contents(__DIR__.'/resources/definition/test1.json');

        $sut = new JsonStrategy();

        $this->assertTrue($sut->supports($json));
        $this->assertEquals([$json], $sut->load($json));
    }

    /**
     * @param string $input
     * @param bool   $result
     * @return void
     *
     * @dataProvider dataSupports
     */
    public function testSupports($input, $result)
    {
        $strategy = new JsonStrategy();
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
                (object) ['a' => 'a'],
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
