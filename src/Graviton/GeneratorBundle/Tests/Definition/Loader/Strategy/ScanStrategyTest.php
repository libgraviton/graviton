<?php
/**
 * test loader and loader strategies
 */

use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Graviton\GeneratorBundle\Definition\Schema\Definition;
use Graviton\GeneratorBundle\Definition\Loader\Strategy\ScanStrategy;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ScanStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * check loading with null input
     *
     * @return void
     */
    public function testLoadDir()
    {
        $file = __DIR__.'/resources/definition/test.json';

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

        $sut = new ScanStrategy($serializer);
        $sut->setScanDir(__DIR__);
        $this->assertTrue($sut->supports(null));

        $data = $sut->load(null);
        $this->assertContainsOnlyInstancesOf('Graviton\GeneratorBundle\Definition\JsonDefinition', $data);
        $this->assertEquals($data, [
            new JsonDefinition((new Definition())->setId('a')),
        ]);
    }
}
