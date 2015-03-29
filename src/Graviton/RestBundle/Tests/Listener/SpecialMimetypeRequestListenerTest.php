<?php

namespace Graviton\RestBundle\Tests\Listener;

use Graviton\RestBundle\Listener\SpecialMimetypeRequestListener;
use Symfony\Component\HttpFoundation\Request;

class SpecialMimetypeRequestListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testOnKernelRequest()
    {
        $server = array('HTTP_ACCEPT' => 'application/vnd.com.swisscom.translation+json');
        $request = new Request(array(), array(), array(), array(), array(), $server);

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
