<?php
/**
 * functional test suite for the EventStatusLinkResponseListener
 */

namespace Graviton\RabbitMqBundle\Tests\Listener;

use Graviton\DocumentBundle\Entity\ExtReference;
use Graviton\RabbitMqBundle\Listener\EventStatusLinkResponseListener;
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
        )->disableOriginalConstructor()->setMethods(['publish', 'getChannel'])->getMockForAbstractClass();

        $producerMock->expects($this->once())->method('publish')
            ->will(
                $this->returnCallback(
                    function ($message, $routingKey) {
                        \PHPUnit_Framework_Assert::assertSame(
                            '{"event":"document.core.product.create","coreUserId":"",'.
                            '"document":{"$ref":"graviton-api-test\/core\/product'.
                            '"},"status":{"$ref":"http:\/\/graviton-test.lo\/worker\/123jkl890yui567mkl"}}',
                            $message
                        );

                        \PHPUnit_Framework_Assert::assertSame(
                            'someWorkerId',
                            $routingKey
                        );
                    }
                )
            );

        $channelMock = $this->getMockBuilder(
            '\PhpAmqpLib\Channel\AMQPChannel'
        )->disableOriginalConstructor()->getMock();

        $producerMock->expects($this->once())->method('getChannel')
            ->willReturn($channelMock);

        $routerMock = $this->getMockBuilder('\Symfony\Component\Routing\RouterInterface')->disableOriginalConstructor(
        )->setMethods(['generate'])->getMockForAbstractClass();
        $routerMock->expects($this->once())->method('generate')->willReturn(
            'http://graviton-test.lo/worker/123jkl890yui567mkl'
        );

        $requestMock = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Request')->disableOriginalConstructor(
        )->setMethods(['get'])->getMock();
        $requestMock->expects($this->atLeastOnce())->method('get')->will(
            $this->returnCallback(
                function () {
                    switch (func_get_arg(0)) {
                        case '_route':
                            return 'graviton.core.rest.product.post';
                            break;
                        case 'selfLink':
                            return 'graviton-api-test/core/product';
                            break;
                    }
                }
            )
        );

        $requestStackMock = $this->getMockBuilder(
            '\Symfony\Component\HttpFoundation\RequestStack'
        )->disableOriginalConstructor()->setMethods(['getCurrentRequest'])->getMock();
        $requestStackMock->expects($this->once())->method('getCurrentRequest')->willReturn($requestMock);

        $cursorMock = $this->getMockBuilder('\Doctrine\MongoDB\CursorInterface')->disableOriginalConstructor(
        )->getMockForAbstractClass();
        $cursorMock->expects($this->any())->method('toArray')->willReturn(['someWorkerId' => 'some content']);

        $queryMock = $this->getMockBuilder('\Doctrine\MongoDB\Query\Query')->disableOriginalConstructor()->getMock();
        $queryMock->expects($this->any())->method('execute')->willReturn($cursorMock);

        $queryBuilderMock = $this->getMockBuilder('\Doctrine\ODM\MongoDB\Query\Builder')->disableOriginalConstructor(
        )->getMock();
        $queryBuilderMock->expects($this->any())->method('select')->willReturnSelf();
        $queryBuilderMock->expects($this->any())->method('field')->willReturnSelf();
        $queryBuilderMock->expects($this->any())->method('equals')->willReturnSelf();
        $queryBuilderMock->expects($this->any())->method('getQuery')->willReturn($queryMock);

        $documentManagerMock = $this->getMockBuilder(
            '\Doctrine\ODM\MongoDB\DocumentManager'
        )->disableOriginalConstructor()->setMethods(['createQueryBuilder', 'persist', 'flush'])->getMock();
        $documentManagerMock->expects($this->any())->method('createQueryBuilder')->willReturn($queryBuilderMock);
        $documentManagerMock->expects($this->any())->method('persist')->with(
            $this->callback(
                function ($obj) {
                    return
                        get_class($obj) == 'GravitonDyn\EventStatusBundle\Document\EventStatus' &&
                        $obj->getCreatedate() instanceof \DateTime &&
                        get_class($obj->getEventresource()) == 'GravitonDyn\EventStatusBundle\Document\\'.
                        'EventStatusEventResourceEmbedded' &&
                        get_class($obj->getEventresource()->getRef()) == 'Graviton\DocumentBundle\Entity\\'.
                        'ExtReference' &&
                        $obj->getEventresource()->getRef()->jsonSerialize() == ['$ref' => 'App', '$id' => 7] &&
                        $obj->getEventname() == 'document.dude.config.create' &&
                        count($obj->getStatus()) === 1 &&
                        count($obj->getInformation()) === 0;
                }
            )
        );
        $documentManagerMock->expects($this->any())->method('flush');

        $extrefConverterMock = $this->getMockBuilder(
            '\Graviton\DocumentBundle\Service\ExtReferenceConverter'
        )->disableOriginalConstructor()->setMethods(['getExtReference'])->getMock();
        $extrefConverterMock->expects($this->exactly(1))->method('getExtReference')
            ->willReturn(ExtReference::create('App', 7));

        $queueEventMock = $this->getMockBuilder(
            '\Graviton\RabbitMqBundle\Document\QueueEvent'
        )->setMethods(['getEvent', 'getDocumenturl'])->getMock();
        $queueEventMock->expects($this->exactly(5))->method('getEvent')->willReturn('document.dude.config.create');
        $queueEventMock->expects($this->exactly(2))->method('getDocumenturl')->willReturn('http://localhost/dude/4');

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
            $extrefConverterMock,
            $queueEventMock,
            [
                'Testing' => [
                    'baseRoute' => 'graviton.core.rest.product',
                    'events' => [
                        'post' => 'document.core.product.create',
                        'put' => 'document.core.product.update',
                        'delete' => 'document.core.product.delete'
                    ]
                ]
            ],
            '\GravitonDyn\EventWorkerBundle\Document\EventWorker',
            '\GravitonDyn\EventStatusBundle\Document\EventStatus',
            '\GravitonDyn\EventStatusBundle\Document\EventStatusStatus',
            '\GravitonDyn\EventStatusBundle\Document\EventStatusEventResourceEmbedded',
            'gravitondyn.eventstatus.rest.eventstatus.get'
        );

        $listener->onKernelResponse($filterResponseEventMock);

        $this->assertEquals(
            '<http://graviton-test.lo/worker/123jkl890yui567mkl>; rel="eventStatus"',
            $response->headers->get('Link')
        );
    }
}
