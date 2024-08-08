<?php
/**
 * Unit tests for the XVersionResponseListener
 */

namespace Graviton\Tests\Rest\Listener;

use Graviton\RestBundle\Listener\XVersionResponseListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @category GravitonCoreBundle
 * @package  Graviton
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class XVersionResponseListenerTest extends TestCase
{
    /**
     * verifies the correct behavior of the onKernelResponse()
     *
     * @return void
     */
    public function testOnKernelResponse()
    {
        $event = new ResponseEvent(
            $this->getMockForAbstractClass(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new Response()
        );

        $listener = new XVersionResponseListener('self: v3.0.0-hans;');
        $listener->onKernelResponse($event);

        $this->assertEquals('self: v3.0.0-hans;', $event->getResponse()->headers->get('X-VERSION'));
    }

    /**
     * verifies the correct behavior of the onKernelResponse()
     *
     * @return void
     */
    public function testOnKernelResponseOnSubRequest()
    {
        $response = new Response();

        $event = new ResponseEvent(
            $this->getMockForAbstractClass(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::SUB_REQUEST,
            new Response()
        );

        $listener = new XVersionResponseListener('self: v3.0.0-hans;');
        $listener->onKernelResponse($event);

        $this->assertFalse($response->headers->has('X-VERSION'));
    }
}
