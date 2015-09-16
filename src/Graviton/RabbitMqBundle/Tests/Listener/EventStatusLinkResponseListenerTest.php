<?php
/**
 * functional test suite for the EventStatusLinkResponseListener
 */

namespace Graviton\RabbitMqBundle\Tests\Listener;

use Graviton\RabbitMqBundle\Listener\EventStatusLinkResponseListener;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class EventStatusLinkResponseListenerTest
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class EventStatusLinkResponseListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Verifies the correct workflow of the ResponseListener
     *
     * @return void
     */
    public function testEventStatusLinkResponseListener()
    {
        $producerMock = $this->getMockBuilder(
            '\OldSound\RabbitMqBundle\RabbitMq\ProducerInterface'
        )->disableOriginalConstructor()->setMethods(['publish'])->getMockForAbstractClass();
        $producerMock->expects($this->once())->method('publish')
        ->will(
            $this->returnCallback(
                function ($message, $routingKey) {
                    \PHPUnit_Framework_Assert::assertSame(
                        $message,
                        '{"event":"document.core.product.create","publicUrl":"graviton-api-test\/core\/product",'.
                        '"statusUrl":"http:\/\/graviton-test.lo\/worker\/123jkl890yui567mkl"}'
                    );

                    \PHPUnit_Framework_Assert::assertSame(
                        $routingKey,
                        'document.dude.config.create'
                    );
                }
            )
        );

        $routerMock = $this->getMockBuilder('\Symfony\Component\Routing\RouterInterface')->disableOriginalConstructor(
        )->setMethods(['generate'])->getMockForAbstractClass();
        $routerMock->expects($this->once())->method('generate')->willReturn(
            'http://graviton-test.lo/worker/123jkl890yui567mkl'
        );

        $requestMock = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')->disableOriginalConstructor(
        )->setMethods(['get'])->getMock();
        $requestMock->expects($this->atLeastOnce())->method('get')->will(
            $this->onConsecutiveCalls(
                'graviton.core.rest.product.post',
                'graviton.core.rest.product.post',
                'graviton-api-test/core/product'
            )
        );
        $requestStackMock = $this->getMockBuilder(
            '\Symfony\Component\HttpFoundation\RequestStack'
        )->disableOriginalConstructor()->setMethods(['getCurrentRequest'])->getMock();
        $requestStackMock->expects($this->once())->method('getCurrentRequest')->willReturn($requestMock);

        $cursorMock = $this->getMockBuilder('\Doctrine\MongoDB\CursorInterface')->disableOriginalConstructor(
        )->getMockForAbstractClass();
        $cursorMock->expects($this->once())->method('toArray')->willReturn(['someWorkerId' => 'some content']);

        $queryMock = $this->getMockBuilder('\Doctrine\MongoDB\Query\Query')->disableOriginalConstructor()->getMock();
        $queryMock->expects($this->once())->method('execute')->willReturn($cursorMock);

        $queryBuilderMock = $this->getMockBuilder('\Doctrine\ODM\MongoDB\Query\Builder')->disableOriginalConstructor(
        )->getMock();
        $queryBuilderMock->expects($this->once())->method('select')->willReturnSelf();
        $queryBuilderMock->expects($this->once())->method('field')->willReturnSelf();
        $queryBuilderMock->expects($this->once())->method('equals')->willReturnSelf();
        $queryBuilderMock->expects($this->once())->method('getQuery')->willReturn($queryMock);

        $documentManagerMock = $this->getMockBuilder(
            '\Doctrine\ODM\MongoDB\DocumentManager'
        )->disableOriginalConstructor()->setMethods(['createQueryBuilder', 'persist', 'flush'])->getMock();
        $documentManagerMock->expects($this->once())->method('createQueryBuilder')->willReturn($queryBuilderMock);
        $documentManagerMock->expects($this->once())->method('persist')->with(
            $this->isInstanceOf('\GravitonDyn\EventStatusBundle\Document\EventStatus')
        );
        $documentManagerMock->expects($this->once())->method('flush');

        $queueEventMock = $this->getMockBuilder(
            '\Graviton\RabbitMqBundle\Document\QueueEvent'
        )->disableOriginalConstructor()->setMethods(['getEvent'])->getMock();
        $queueEventMock->expects($this->exactly(3))->method('getEvent')->willReturn('document.dude.config.create');

        $filterResponseEventMock = $this->getMockBuilder(
            '\Symfony\Component\HttpKernel\Event\FilterResponseEvent'
        )->disableOriginalConstructor()->setMethods(['isMasterRequest', 'getResponse'])->getMock();
        $filterResponseEventMock->expects($this->once())->method('isMasterRequest')->willReturn(true);

        $response = new Response();
        $filterResponseEventMock->expects($this->once())->method('getResponse')->willReturn($response);

        $listener = new EventStatusLinkResponseListener(
            $producerMock,
            $routerMock,
            $requestStackMock,
            $documentManagerMock,
            $queueEventMock,
            '\GravitonDyn\EventWorkerBundle\Document\EventWorker',
            '\GravitonDyn\EventStatusBundle\Document\EventStatus',
            '\GravitonDyn\EventStatusBundle\Document\EventStatusStatus',
            'gravitondyn.eventstatus.rest.eventstatus.get'
        );

        $listener->onKernelResponse($filterResponseEventMock);

        $this->assertEquals(
            '<http://graviton-test.lo/worker/123jkl890yui567mkl>; rel="eventStatus"',
            $response->headers->get('Link')
        );
    }
}
