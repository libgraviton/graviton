<?php
/**
 * test loader and loader strategies
 */

namespace Graviton\GeneratorBundle\Tests\Definition\Strategy;

use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Graviton\GeneratorBundle\Definition\Schema\Definition;
use Graviton\GeneratorBundle\Definition\Loader\Strategy\FileStrategy;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class FileStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * check if loading from a single file works
     *
     * @return void
     */
    public function testLoadReturnsSingleFileArray()
    {
        $file = __DIR__.'/test.json';

        $serializer = $this
            ->getMockBuilder('Jms\\Serializer\\SerializerInterface')
            ->disableOriginalConstructor()
            ->setMethods(['serialize', 'deserialize'])
            ->getMock();
        $serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with(
                file_get_contents($file),
                'Graviton\GeneratorBundle\Definition\Schema\Definition',
                'json'
            )
            ->will($this->returnValue(
                (new Definition())->setId('a')
            ));

        $sut = new FileStrategy($serializer);
        $this->assertTrue($sut->supports($file));

        $data = $sut->load($file);
        $this->assertContainsOnlyInstancesOf('Graviton\GeneratorBundle\Definition\JsonDefinition', $data);
        $this->assertEquals($data, [
            new JsonDefinition((new Definition())->setId('a')),
        ]);
    }
}
