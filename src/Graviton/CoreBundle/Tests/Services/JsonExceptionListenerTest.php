<?php
/**
 * test a JsonExceptionLister
 */

namespace Graviton\CoreBundle\Tests\Services;

use Graviton\CoreBundle\Listener\JsonExceptionListener;
use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * Functional test
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class JsonExceptionListenerTest extends KernelTestCase
{

    /**
     * test normal handling
     *
     * @return void
     */
    public function testStatus500()
    {
        $sut = new JsonExceptionListener();

        $exceptionEvent = $this->getMockBuilder(ExceptionEvent::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getException'])
            ->getMock();

        $exception = new \Exception('This is the exception message', 501);
        $exceptionEvent->method('getException')->willReturn($exception);

        $sut->onKernelException($exceptionEvent);

        $response = $exceptionEvent->getResponse();
        $this->assertInstanceOf(JsonResponse::class, $response);

        $this->assertEquals(
            '{"code":501,"exceptionClass":"Exception","message":"This is the exception message"}',
            $response->getContent()
        );
    }
}
