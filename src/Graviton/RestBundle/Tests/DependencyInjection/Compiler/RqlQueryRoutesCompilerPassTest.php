<?php
/**
 * RqlQueryRoutesCompilerPassTest class file
 */

namespace Graviton\RestBundle\Tests\DependencyInjection\Compiler;

use Graviton\RestBundle\DependencyInjection\Compiler\RqlQueryRoutesCompilerPass;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class RqlQueryRoutesCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test RqlQueryRoutesCompilerPass::process()
     *
     * @return void
     */
    public function testProcess()
    {
        $services = [
            'namespace1.bundle1.unused.service1' => [],
            'namespace2.bundle2.unused.service2' => [],
        ];
        $routes = [
            'namespace1.bundle1.rest.service1.all',
            'namespace1.bundle1.rest.service1.get',
            'namespace2.bundle2.rest.service2.all',
            'namespace2.bundle2.rest.service2.get',
        ];

        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $container
            ->expects($this->once())
            ->method('getParameter')
            ->with('graviton.rest.services')
            ->willReturn($services);
        $container
            ->expects($this->once())
            ->method('setParameter')
            ->with('graviton.rest.listener.rqlqueryrequestlistener.allowedroutes', $routes);

        $sut = new RqlQueryRoutesCompilerPass();
        $sut->process($container);
    }
}
