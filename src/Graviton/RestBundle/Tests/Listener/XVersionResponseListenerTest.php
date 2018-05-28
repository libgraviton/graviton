<?php
/**
 * Unit tests for the XVersionResponseListener
 */

namespace Graviton\RestBundle\Listener;

use Symfony\Component\HttpFoundation\Response;

/**
 * @category GravitonCoreBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class XVersionResponseListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * verifies the correct behavior of the onKernelResponse()
     *
     * @return void
     */
    public function testOnKernelResponse()
    {
        $response = new Response();

        $eventDouble = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\FilterResponseEvent')
            ->disableOriginalConstructor()
            ->setMethods(array('getResponse', 'isMasterRequest'))
            ->getMock();
        $eventDouble
            ->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue($response));
        $eventDouble
            ->expects($this->once())
            ->method('isMasterRequest')
            ->will($this->returnValue(true));

        $listener = new XVersionResponseListener('self: v3.0.0-hans;');
        $listener->onKernelResponse($eventDouble);

        $this->assertEquals('self: v3.0.0-hans;', $response->headers->get('X-VERSION'));
    }

    /**
     * verifies the correct behavior of the onKernelResponse()
     *
     * @return void
     */
    public function testOnKernelResponseOnSubRequest()
    {
        $response = new Response();

        $eventDouble = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\FilterResponseEvent')
            ->disableOriginalConstructor()
            ->setMethods(array('isMasterRequest'))
            ->getMock();
        $eventDouble
            ->expects($this->once())
            ->method('isMasterRequest')
            ->will($this->returnValue(false));

        $listener = new XVersionResponseListener('self: v3.0.0-hans;');
        $listener->onKernelResponse($eventDouble);

        $this->assertNull($response->headers->get('X-VERSION'));
    }
}
