<?php
/**
 * test loader and loader strategies
 */

namespace Graviton\GeneratorBundle\Tests\Definition;

use Graviton\GeneratorBundle\Definition\JsonDefinition;
use Graviton\GeneratorBundle\Definition\Loader\Loader;
use Graviton\GeneratorBundle\Definition\Schema\Definition;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class LoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * check if strategies are called
     *
     * @return void
     */
    public function testLoadCallsStrategy()
    {
        $json = __METHOD__;
        $definition = new Definition();

        $serializer = $this->getMockBuilder('Jms\Serializer\SerializerInterface')
            ->disableOriginalConstructor()
            ->setMethods(['serialize', 'deserialize'])
            ->getMock();
        $serializer->expects($this->once())
            ->method('deserialize')
            ->with($json, 'Graviton\GeneratorBundle\Definition\Schema\Definition', 'json')
            ->willReturn($definition);

        $strategy = $this->getMockBuilder('Graviton\GeneratorBundle\Definition\Loader\Strategy\StrategyInterface')
            ->getMock();
        $strategy->expects($this->once())
            ->method('supports')
            ->with(null)
            ->will($this->returnValue(true));
        $strategy->expects($this->once())
            ->method('load')
            ->with(null)
            ->will($this->returnValue([$json]));

        $sut = new Loader($serializer);
        $sut->addStrategy($strategy);
        $this->assertEquals([new JsonDefinition($definition)], $sut->load(null));
    }
}
