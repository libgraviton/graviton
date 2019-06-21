<?php
/**
 * RqlOperatorNotAllowedListenerTest class file
 */

namespace Graviton\ExceptionBundle\Tests\Listener;

use Graviton\ExceptionBundle\Exception\RqlOperatorNotAllowedException;
use Graviton\ExceptionBundle\Listener\RqlOperatorNotAllowedListener;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RqlOperatorNotAllowedListenerTest extends TestCase
{
    /**
     * @var GetResponseForExceptionEvent
     */
    private $event;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var SerializationContext
     */
    private $context;

    /**
     * Setup the test
     *
     * @return void
     */
    protected function setUp() : void
    {
        $this->event = $this->getMockBuilder(GetResponseForExceptionEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializer = $this->getMockBuilder(SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context = $this->getMockBuilder(SerializationContext::class)
            ->disableOriginalConstructor()
            ->getMock();

        parent::setUp();
    }

    /**
     * Test RqlOperatorNotAllowedListener::onKernelException() with unsupported exception class
     *
     * @return void
     */
    public function testOnKernelExceptionWithUnsupportedException()
    {
        $this->event->expects($this->once())
            ->method('getException')
            ->willReturn(new HttpException(400));
        $this->event->expects($this->never())
            ->method('setResponse');

        $listener = new RqlOperatorNotAllowedListener($this->serializer, $this->context);
        $listener->onKernelException($this->event);
    }

    /**
     * Test RqlOperatorNotAllowedListener::onKernelException()
     *
     * @return void
     */
    public function testOnKernelException()
    {
        $serializedContent = 'serialized content';

        $response = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();
        $response->expects($this->once())
            ->method('setStatusCode')
            ->with(Response::HTTP_BAD_REQUEST)
            ->willReturnSelf();
        $response->expects($this->once())
            ->method('setContent')
            ->with($serializedContent)
            ->willReturnSelf();

        $exception = new RqlOperatorNotAllowedException('limit');
        $exception->setResponse($response);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with(['message' => $exception->getMessage()], 'json')
            ->willReturn($serializedContent);

        $this->event->expects($this->once())
            ->method('getException')
            ->willReturn($exception);
        $this->event->expects($this->once())
            ->method('setResponse');

        $listener = new RqlOperatorNotAllowedListener($this->serializer);
        $listener->onKernelException($this->event);
    }
}
