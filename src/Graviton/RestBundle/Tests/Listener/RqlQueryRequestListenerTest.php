<?php
/**
 * RqlQueryRequestListenerTest class file
 */

namespace Graviton\RestBundle\Tests\Listener;

use Graviton\RestBundle\Listener\RqlQueryRequestListener;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class RqlQueryRequestListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test RqlQueryRequestListener::onKernelRequest()
     *
     * @param string $routeId   Current route ID
     * @param bool   $isAllowed Is RQL parsing allowed
     * @return void
     * @dataProvider dataOnKernelRequest
     */
    public function testOnKernelRequest($routeId, $isAllowed)
    {
        $innerListener = $this->getMockBuilder('Graviton\RqlParserBundle\Listener\RequestListener')
            ->disableOriginalConstructor()
            ->getMock();
        $innerListener->expects($isAllowed ? $this->once() : $this->never())
            ->method('onKernelRequest');

        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn(new Request([], [], ['_route' => $routeId]));

        $listener = new RqlQueryRequestListener($innerListener, ['allowed.route.id']);
        $listener->onKernelRequest($event);
    }

    /**
     * Data RqlQueryRequestListener::onKernelRequest()
     *
     * @return array
     */
    public function dataOnKernelRequest()
    {
        return [
            [
                'not.allowed.route.id',
                false,
            ],
            [
                'allowed.route.id',
                true,
            ],
        ];
    }
}
