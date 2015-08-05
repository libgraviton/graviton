<?php
/**
 * RqlSelectResponseListenerTest class file
 */

namespace Graviton\RestBundle\Tests\Listener;

use Graviton\RestBundle\Listener\RqlSelectResponseListener;
use Graviton\RestBundle\Utils\ObjectSlicer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Xiag\Rql\Parser\Node\SelectNode;
use Xiag\Rql\Parser\Query;

/**
 * RqlSelectResponseListener test
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class RqlSelectResponseListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testOnKernelResponseWithoutRql()
    {
        $request = new Request();
        $response = new Response();

        $this->handleInvalidEvent($request, $response);
    }

    /**
     * @return void
     */
    public function testOnKernelResponseWithoutRqlSelect()
    {
        $query = new Query();

        $request = new Request();
        $request->attributes->set('rqlQuery', $query);
        $response = new Response();

        $this->handleInvalidEvent($request, $response);
    }

    /**
     * @return void
     */
    public function testOnKernelResponseWithNon200()
    {
        $query = new Query();
        $query->setSelect(new SelectNode(['a', 'b']));

        $request = new Request();
        $request->attributes->set('rqlQuery', $query);
        $response = new Response();
        $response->setStatusCode(400);

        $this->handleInvalidEvent($request, $response);
    }

    /**
     * @return void
     */
    public function testOnKernelResponseWithInvalidJson()
    {
        $query = new Query();
        $query->setSelect(new SelectNode(['a', 'b']));

        $request = new Request();
        $request->attributes->set('rqlQuery', $query);
        $response = new Response();
        $response->setContent('"abc"');

        $this->handleInvalidEvent($request, $response);
    }

    /**
     * @return void
     */
    public function testOnKernelResponseProcessed()
    {
        $query = new Query();
        $query->setSelect(new SelectNode(['a', 'b']));

        $request = new Request();
        $request->attributes->set('rqlQuery', $query);
        $response = new Response();
        $response->setContent('[{"a":1},{"b":2}]');

        $this->handleValidEvent($request, $response, [__METHOD__, __METHOD__]);
        $this->assertEquals(
            json_encode([__METHOD__, __METHOD__]),
            $response->getContent()
        );
    }

    /**
     * Handle invalid event
     *
     * @param Request  $request  Request
     * @param Response $response response
     *
     * @return void
     */
    private function handleInvalidEvent(Request $request, Response $response)
    {
        /** @var FilterResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterResponseEvent')
            ->disableOriginalConstructor()
            ->setMethods(['getResponse', 'getRequest'])
            ->getMock();
        $event
            ->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));
        $event
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        /** @var ObjectSlicer|\PHPUnit_Framework_MockObject_MockObject $slicer */
        $slicer = $this->getMockBuilder('Graviton\RestBundle\Utils\ObjectSlicer')
            ->setMethods(['sliceMulti'])
            ->getMock();
        $slicer
            ->expects($this->never())
            ->method('sliceMulti');

        $listener = new RqlSelectResponseListener($slicer);
        $listener->onKernelResponse($event);
    }

    /**
     * Handle valid event
     *
     * @param Request  $request     Request
     * @param Response $response    response
     * @param array    $returnValue Slicer result
     *
     * @return void
     */
    private function handleValidEvent(Request $request, Response $response, array $returnValue)
    {
        /** @var FilterResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterResponseEvent')
            ->disableOriginalConstructor()
            ->setMethods(['getResponse', 'getRequest'])
            ->getMock();
        $event
            ->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue($response));
        $event
            ->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        /** @var ObjectSlicer|\PHPUnit_Framework_MockObject_MockObject $slicer */
        $slicer = $this->getMockBuilder('Graviton\RestBundle\Utils\ObjectSlicer')
            ->setMethods(['sliceMulti'])
            ->getMock();
        $slicer
            ->expects($this->once())
            ->method('sliceMulti')
            ->with(
                json_decode($response->getContent()),
                $request->attributes->get('rqlQuery')->getSelect()->getFields()
            )
            ->will($this->returnValue($returnValue));

        $listener = new RqlSelectResponseListener($slicer);
        $listener->onKernelResponse($event);
    }
}
