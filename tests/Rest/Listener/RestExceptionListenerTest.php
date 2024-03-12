<?php
/**
 * RestExceptionListenerTest class file
 */

namespace Graviton\Tests\Rest\Listener;

use Graviton\RestBundle\Exception\RqlOperatorNotAllowedException;
use Graviton\RestBundle\Listener\RestExceptionListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RestExceptionListenerTest extends TestCase
{

    /**
     * gets exception
     *
     * @param \Throwable $t throwable
     *
     * @return ExceptionEvent event
     */
    private function getExceptionEvent(\Throwable $t): ExceptionEvent
    {
        return new ExceptionEvent(
            $this->getMockBuilder(HttpKernelInterface::class)->getMock(),
            new Request(),
            0,
            $t
        );
    }

    /**
     * Test RqlOperatorNotAllowedListener::onKernelException() with unsupported exception class
     *
     * @return void
     */
    public function testOnKernelExceptionWithUnsupportedException()
    {
        $listener = new RestExceptionListener();
        $event = $this->getExceptionEvent(new HttpException(400));
        $listener->onKernelException($event);
        $this->assertNull($event->getResponse());
    }

    /**
     * Test RqlOperatorNotAllowedListener::onKernelException()
     *
     * @return void
     */
    public function testOnKernelException()
    {
        $exception = new RqlOperatorNotAllowedException('limit');

        $listener = new RestExceptionListener();

        $event = $this->getExceptionEvent($exception);
        $listener->onKernelException($event);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $event->getResponse()->getStatusCode());

        $content = json_decode($event->getResponse()->getContent(), true);

        $this->assertEquals(
            "Graviton\RestBundle\Exception\RqlOperatorNotAllowedException",
            $content['type']
        );
        $this->assertEquals(
            "RQL operator \"limit\" is not allowed for this request",
            $content['message']
        );
    }
}
