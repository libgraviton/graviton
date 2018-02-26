<?php
/**
 * validate finding tagged services
 *
 * important because our autorouting hinges on it
 */

namespace Graviton\RestBundle\Tests\DependencyInjection\Compiler;

use Graviton\RestBundle\DependencyInjection\Compiler\RestServicesCompilerPass;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RestServicesCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * validate that process creates an array parameter
     *
     * @dataProvider setsParamsProvider
     *
     * @param array $config service config
     *
     * @return void
     */
    public function testSetsParams($config)
    {
        $container = $this->getMockBuilder('\Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $container
            ->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with($this->equalTo('graviton.rest'))
            ->will($this->returnValue($config));
        $container
            ->expects($this->once())
            ->method('setParameter')
            ->with($this->equalTo('graviton.rest.services', 1), $this->equalTo($config));

        $sut = new RestServicesCompilerPass();
        $sut->process($container);
    }

    /**
     * test cases for testSetsParams
     *
     * @return array
     */
    public function setsParamsProvider()
    {
        return array(
            array(array()),
            array(array('graviton.rest.test', array('read-only' => true))),
        );
    }
}
