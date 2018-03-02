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
class XVersionResponseListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * verifies the correct behavior of the onKernelResponse()
     *
     * @return void
     */
    public function testOnKernelResponse()
    {
        $response = new Response();
        $version = '0.1.0-alpha';

        $eventDouble = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\FilterResponseEvent')
            ->disableOriginalConstructor()
            ->setMethods(array('getResponse', 'isMasterRequest'))
            ->getMock();
        $eventDouble
            ->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue($response));$eventDouble
            ->expects($this->once())
            ->method('isMasterRequest')
            ->will($this->returnValue(true));

        $loggerDouble = $this->getMockBuilder('\Psr\Log\LoggerInterface')
            ->setMethods(array('warning'))
            ->getMockForAbstractClass();
        $loggerDouble
            ->expects($this->any())
            ->method('warning')
            ->with($this->contains('Unable to extract version from composer.json file'));

        $serviceDouble = $this->getMockBuilder('\Graviton\CoreBundle\Service\CoreUtils')
            ->setMethods(array('getVersionInHeaderFormat'))
            ->setConstructorArgs(array(__DIR__ . '/../../../../../app/cache/test'))
            ->getMock();
        $serviceDouble
            ->expects($this->once())
            ->method('getVersionInHeaderFormat')
            ->will($this->returnValue('0.1.0-alpha'));

        $listener = new XVersionResponseListener($serviceDouble, $loggerDouble);
        $listener->onKernelResponse($eventDouble);

        $this->assertEquals($version, $response->headers->get('X-VERSION'));
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

        $loggerDouble = $this->getMockForAbstractClass('\Psr\Log\LoggerInterface');
        $serviceDouble = $this->createMock(
            '\Graviton\CoreBundle\Service\CoreUtils',
            [],
            array(__DIR__ . '/../../../../../app/cache/test')
        );

        $listener = new XVersionResponseListener($serviceDouble, $loggerDouble);
        $listener->onKernelResponse($eventDouble);

        $this->assertNull($response->headers->get('X-VERSION'));
    }
}
