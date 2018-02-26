<?php
/**
 * Test suite for mimetype registering service
 */

namespace Graviton\RestBundle\Tests\Listener;

use Graviton\RestBundle\Listener\SpecialMimetypeRequestListener;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class SpecialMimetypeRequestListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testOnKernelRequest()
    {
        $server = array('HTTP_ACCEPT' => 'application/vnd.com.swisscom.translation+json');
        $request = new Request(array(), array(), array(), array(), array(), $server);
        $request->setFormat('json', 'application/json');

        $eventDouble = $this->getMockBuilder('\Graviton\RestBundle\Event\RestEvent')
            ->disableOriginalConstructor()
            ->setMethods(array('getRequest'))
            ->getMock();
        $eventDouble
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $containerDouble = $this->getMockBuilder('\Symfony\Component\DependencyInjection\Container')
            ->disableOriginalConstructor()
            ->setMethods(array('getParameter'))
            ->getMock();
        $containerDouble
            ->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('graviton.rest.special_mimetypes'))
            ->willReturn(array('json' => array('application/vnd.com.swisscom.translation+json')));

        $listener = new SpecialMimetypeRequestListener($containerDouble);
        $listener->onKernelRequest($eventDouble);

        $this->assertEquals('json', $request->getFormat('application/vnd.com.swisscom.translation+json'));
    }
}
