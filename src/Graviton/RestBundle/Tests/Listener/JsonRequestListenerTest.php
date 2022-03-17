<?php

namespace Graviton\RestBundle\Tests\Listener;

use Graviton\RestBundle\Event\RestEvent;
use Graviton\RestBundle\Listener\JsonRequestListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class JsonRequestListenerTest extends TestCase
{
    /**
     * @return void
     */
    public function testOnKernelRequest()
    {
        $server = array('HTTP_ACCEPT' => 'application/json');
        $request = new Request([], [], [], [], [], $server);

        $event = new RestEvent();
        $event->setRequest($request);

        $listener = new JsonRequestListener();
        $listener->onKernelRequest($event);

        $this->assertEquals('json', $request->getRequestFormat());
    }
}
