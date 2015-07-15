<?php
/**
 * test loader and loader strategies
 */

namespace Graviton\GeneratorBundle\Tests\Definition\Strategy;

use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Graviton\GeneratorBundle\Definition\Schema\Definition;
use Graviton\GeneratorBundle\Definition\Loader\Strategy\DirStrategy;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DirStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test loading multiple files from dir
     *
     * @return void
     */
    public function testLoadDir()
    {
        $dir = __DIR__.'/dir';

        $serializer = $this
            ->getMockBuilder('Jms\\Serializer\\SerializerInterface')
            ->disableOriginalConstructor()
            ->setMethods(['serialize', 'deserialize'])
            ->getMock();
        $serializer
            ->expects($this->exactly(2))
            ->method('deserialize')
            ->withConsecutive(
                [
                    file_get_contents($dir.'/test1.json'),
                    'Graviton\GeneratorBundle\Definition\Schema\Definition',
                    'json',
                ],
                [
                    file_get_contents($dir.'/test2.json'),
                    'Graviton\GeneratorBundle\Definition\Schema\Definition',
                    'json',
                ]
            )
            ->will(
                $this->onConsecutiveCalls(
                    (new Definition())->setId('a'),
                    (new Definition())->setId('b')
                )
            );

        $sut = new DirStrategy($serializer);
        $this->assertTrue($sut->supports($dir));

        $data = $sut->load($dir);
        $this->assertContainsOnlyInstancesOf('Graviton\GeneratorBundle\Definition\JsonDefinition', $data);
        $this->assertEquals(
            $data,
            [
                new JsonDefinition((new Definition())->setId('a')),
                new JsonDefinition((new Definition())->setId('b')),
            ]
        );
    }
}
