<?php

namespace Graviton\RestBundle\Tests\Listener;

use Graviton\RestBundle\Listener\JsonRequestListener;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class JsonRequestListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testOnKernelRequest()
    {
        $server = array('HTTP_ACCEPT' => 'application/json');
        $request = new Request(array(), array(), array(), array(), array(), $server);

        $eventDouble = $this->getMockBuilder('\Graviton\RestBundle\Event\RestEvent')
            ->disableOriginalConstructor()
            ->setMethods(array('getRequest'))
            ->getMock();
        $eventDouble
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $listener = new JsonRequestListener();
        $listener->onKernelRequest($eventDouble);

        $this->assertEquals('json', $request->getRequestFormat());
    }
}
