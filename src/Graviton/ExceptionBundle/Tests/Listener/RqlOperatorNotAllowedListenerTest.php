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
class RqlOperatorNotAllowedListenerTest extends TestCase
{
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
        $this->serializer = $this->getMockBuilder(SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context = $this->getMockBuilder(SerializationContext::class)
            ->disableOriginalConstructor()
            ->getMock();

        parent::setUp();
    }

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
            $this->getMockForAbstractClass(HttpKernelInterface::class),
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
        $listener = new RqlOperatorNotAllowedListener($this->serializer, $this->context);
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
        $serializedContent = 'serialized content';

        $response = new Response($serializedContent, Response::HTTP_BAD_REQUEST);
        $exception = new RqlOperatorNotAllowedException('limit');
        $exception->setResponse($response);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with(['message' => $exception->getMessage()], 'json')
            ->willReturn($serializedContent);

        $listener = new RqlOperatorNotAllowedListener($this->serializer);

        $event = $this->getExceptionEvent($exception);

        $listener->onKernelException($event);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $event->getResponse()->getStatusCode());
        $this->assertEquals($serializedContent, $event->getResponse()->getContent());
    }
}
