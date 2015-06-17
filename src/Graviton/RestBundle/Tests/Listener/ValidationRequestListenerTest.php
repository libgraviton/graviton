<?php
/**
 * validate ValidationRequestListener
 */

namespace Graviton\RestBundle\Tests\Listener;

use Graviton\RestBundle\Listener\ValidationRequestListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ValidationRequestListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testWillNotUnpackNonJsonData()
    {
        $server = array('HTTP_ACCEPT' => 'text/html');
        $request = new Request(array(), array(), array(), array(), array(), $server);

        $eventDouble = $this->getMockBuilder('\Graviton\RestBundle\Event\RestEvent')
            ->disableOriginalConstructor()
            ->setMethods(array('getRequest'))
            ->getMock();
        $eventDouble
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $listener = new ValidationRequestListener;
        $listener->onKernelRequest($eventDouble);

        $this->assertEquals('html', $request->getRequestFormat());
    }

    /**
     * @dataProvider willUnpackJsonData
     * @expectedException Graviton\ExceptionBundle\Exception\NoInputException
     *
     * @param string $contentType Content-Type header
     *
     * @return void
     */
    public function testWillUnpackJson($contentType)
    {
        $server = array('HTTP_CONTENT_TYPE' => $contentType);
        $request = new Request(array(), array(), array(), array(), array(), $server, null);
        $request->setMethod('POST');

        $eventDouble = $this->getMockBuilder('\Graviton\RestBundle\Event\RestEvent')
            ->disableOriginalConstructor()
            ->setMethods(array('getRequest', 'getController', 'getResponse'))
            ->getMock();
        $eventDouble
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);
        $eventDouble
            ->expects($this->once())
            ->method('getController')
            ->willReturn($request);
        $eventDouble
            ->expects($this->once())
            ->method('getResponse')
            ->willReturn(new Response);

        $listener = new ValidationRequestListener;
        $listener->onKernelRequest($eventDouble);

        $this->assertEquals('html', $request->getRequestFormat());
    }

    /**
     * @return array
     */
    public function willUnpackJsonData()
    {
        return [
            ['application/json; charset=UTF-8'],
            ['application/json'],
            ['application/JSON'],
        ];
    }
}
