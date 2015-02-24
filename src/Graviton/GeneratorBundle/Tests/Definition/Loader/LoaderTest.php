<?php
/**
 * test loader and loader strategies
 */

namespace Graviton\GeneratorBundle\Tests\Definition;

use Graviton\GeneratorBundle\Definition\Loader\Loader;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class LoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadCallsStrategy()
    {
        $jsonDef = array(
            $this
                ->getMockBuilder('\Graviton\GeneratorBundle\Definition\JsonDefinition')
                ->disableOriginalConstructor()
                ->getMock()
            ,
        );
        $strategy = $this->getMock('\Graviton\GeneratorBundle\Definition\Loader\Strategy\StrategyInterface');

        $strategy
            ->expects($this->once())
            ->method('supports')
            ->with(null)
            ->will($this->returnValue(true))
        ;

        $strategy
            ->expects($this->once())
            ->method('load')
            ->with(null)
            ->will($this->returnValue($jsonDef))
        ;

        $sut = new Loader;
        $sut->addStrategy($strategy);
        $this->assertEquals($jsonDef, $sut->load(null));
    }
}
