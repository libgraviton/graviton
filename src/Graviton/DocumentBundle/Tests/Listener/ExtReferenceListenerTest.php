<?php
/**
 * ExtReferenceListenerTest class file
 */

namespace Graviton\DocumentBundle\Tests\Listener;

use Graviton\DocumentBundle\Listener\ExtReferenceListener;
use Graviton\DocumentBundle\Service\ExtReferenceConverterInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ExtReferenceListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExtReferenceConverterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $converter;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var ParameterBag|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestAttrs;
    /**
     * @var Response|\PHPUnit_Framework_MockObject_MockObject
     */
    private $response;
    /**
     * @var FilterResponseEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseEvent;
    /**
     * @var HeaderBag|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseHeaders;
    /**
     * @var RequestStack|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestStack;


    /**
     * setup type we want to test
     *
     * @return void
     */
    public function setUp()
    {
        $this->converter = $this->getMockBuilder('\Graviton\DocumentBundle\Service\ExtReferenceConverterInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getUrl', 'getDbRef'])
            ->getMock();

        $this->requestAttrs = $this->getMockBuilder('\Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $this->request = new Request();
        $this->request->attributes = $this->requestAttrs;

        $this->responseHeaders = $this->getMockBuilder('\Symfony\Component\HttpFoundation\HeaderBag')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $this->response = $this->getMockBuilder('\Symfony\Component\HttpFoundation\Response')
            ->disableOriginalConstructor()
            ->setMethods(['getContent', 'setContent'])
            ->getMock();
        $this->response->headers = $this->responseHeaders;

        $this->responseEvent = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\FilterResponseEvent')
            ->disableOriginalConstructor()
            ->setMethods(['getResponse', 'isMasterRequest'])
            ->getMock();
        $this->responseEvent
            ->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->response);

        $this->requestStack = $this->getMockBuilder('\Symfony\Component\HttpFoundation\RequestStack')
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentRequest'])
            ->getMock();
        $this->requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($this->request);
    }

    /**
     * Create listener
     *
     * @param array $fields Field mapping
     * @return ExtReferenceListener
     */
    private function createListener(array $fields)
    {
        return new ExtReferenceListener($this->converter, $fields, $this->requestStack);
    }

    /**
     * Test onKernelResponse
     *
     * @return void
     */
    public function testOnKernelResponseNonMasterRequest()
    {
        $this->response->expects($this->never())
            ->method('setContent');
        $this->response->expects($this->once())
            ->method('getContent')
            ->willReturn('non empty');
        $this->responseEvent->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(false);
        $this->responseHeaders->expects($this->never())
            ->method('get');

        $listener = $this->createListener([]);
        $listener->onKernelResponse($this->responseEvent);
    }

    /**
     * Test onKernelResponse
     *
     * @return void
     */
    public function testOnKernelResponseEmptyContent()
    {
        $this->response->expects($this->never())
            ->method('setContent');
        $this->response->expects($this->once())
            ->method('getContent')
            ->willReturn('');
        $this->responseEvent->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(true);
        $this->responseHeaders->expects($this->never())
            ->method('get');

        $listener = $this->createListener([]);
        $listener->onKernelResponse($this->responseEvent);
    }

    /**
     * Test onKernelResponse
     *
     * @return void
     */
    public function testOnKernelResponseNonJsonResponse()
    {
        $this->response->expects($this->never())
            ->method('setContent');
        $this->response->expects($this->once())
            ->method('getContent')
            ->willReturn('non empty');
        $this->responseEvent->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(true);
        $this->responseHeaders->expects($this->once())
            ->method('get')
            ->willReturn('text/plain');

        $listener = $this->createListener([]);
        $listener->onKernelResponse($this->responseEvent);
    }

    /**
     * Test onKernelResponse
     *
     * @return void
     */
    public function testOnKernelResponse()
    {
        $before = [
            'name' => 'name',
            'ref' => '{"$ref":"toplevel","$id":123}',
            'array' => [
                '{"$ref":"array","$id":123}',
                '{"$ref":"array","$id":456}',
            ],
            'arrayhash' => [
                [
                    'ref' => '{"$ref":"arrayhash","$id":123}',
                ],
                [
                    'ref' => '{"$ref":"arrayhash","$id":456}',
                ],
            ],
            'hash' => [
                'c' => [
                    'ref' => '{"$ref":"hash","$id":123}',
                ],
                'd' => [
                    'ref' => '{"$ref":"notmapped","$id":123}',
                ],
            ],
            'deep' => [
                'deep' => [
                    'deep' => [
                        'deep' => [
                            'ref' => '{"$ref":"deep","$id":123}',
                        ],
                    ],
                ],
            ],
        ];
        $after = [
            'name' => 'name',
            'ref' => 'url-toplevel-123',
            'array' => [
                'url-array-123',
                'url-array-456',
            ],
            'hash' => [
                'c' => [
                    'ref' => 'url-hash-123',
                ],
                'd' => [
                    'ref' => '{"$ref":"notmapped","$id":123}',
                ],
            ],
            'arrayhash' => [
                [
                    'ref' => 'url-arrayhash-123',
                ],
                [
                    'ref' => 'url-arrayhash-456',
                ],
            ],
            'deep' => [
                'deep' => [
                    'deep' => [
                        'deep' => [
                            'ref' => 'url-deep-123',
                        ],
                    ],
                ],
            ],
        ];
        $fields = [
            'route.id' => [
                'ref',
                'array.0',
                'hash.c.ref',
                'arrayhash.0.ref',
                'deep.deep.deep.deep.ref',
            ]
        ];

        $this->response->expects($this->once())
            ->method('setContent')
            ->with(new \PHPUnit_Framework_Constraint_JsonMatches(json_encode($after)));
        $this->response->expects($this->any())
            ->method('getContent')
            ->willReturn(json_encode($before));
        $this->responseEvent->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(true);
        $this->responseHeaders->expects($this->once())
            ->method('get')
            ->willReturn('application/json');
        $this->requestAttrs
            ->expects($this->any())
            ->method('get')
            ->with('_route')
            ->willReturn('route.id');
        $this->converter
            ->expects($this->any())
            ->method('getUrl')
            ->willReturnCallback(function ($url) {
                $map = [
                    '{"$ref":"toplevel","$id":123}' => 'url-toplevel-123',

                    '{"$ref":"array","$id":123}' => 'url-array-123',
                    '{"$ref":"array","$id":456}' => 'url-array-456',

                    '{"$ref":"hash","$id":123}' => 'url-hash-123',

                    '{"$ref":"arrayhash","$id":123}' => 'url-arrayhash-123',
                    '{"$ref":"arrayhash","$id":456}' => 'url-arrayhash-456',

                    '{"$ref":"deep","$id":123}' => 'url-deep-123',
                ];

                return $map[json_encode($url)];
            });

        $listener = $this->createListener($fields);
        $listener->onKernelResponse($this->responseEvent);
    }
}
