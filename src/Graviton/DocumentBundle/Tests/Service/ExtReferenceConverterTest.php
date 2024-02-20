<?php
/**
 * ExtReferenceConverterTest class file
 */

namespace Graviton\DocumentBundle\Tests\Service;

use Graviton\DocumentBundle\Entity\ExtReference;
use Graviton\DocumentBundle\Service\ExtReferenceConverter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

/**
 * ExtReferenceConverter test
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class ExtReferenceConverterTest extends TestCase
{
    /**
     * @var RouterInterface|MockObject
     */
    private $router;

    /**
     * @var array
     */
    private $routeInformation = [];

    /**
     * setup type we want to test
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->router = $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['generate', 'matchRequest', 'getContext'])
            ->getMock();

        $this->router
            ->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue(RequestContext::fromUri('/')));

        $this->routeInformation['/core/app/test'] = [
            'collection' => 'App',
            'id' => 'test'
        ];
        $this->routeInformation['/hans/showcase/blah'] = [
            'collection' => 'ShowCase',
            'id' => 'blah'
        ];
    }

    /**
     * verify that we get a mongodbref
     *
     * @dataProvider getExtReferenceProvider
     *
     * @param string       $url          extref url
     * @param ExtReference $extReference extref object
     *
     * @return void
     */
    public function testGetExtReference($url, ExtReference $extReference)
    {
        $this->router
            ->expects($this->once())
            ->method('matchRequest')
            ->willReturnCallback(
                function (Request $request) {
                    return $this->routeInformation[$request->getPathInfo()];
                }
            );

        $converter = new ExtReferenceConverter(
            $this->router
        );
        $this->assertEquals($extReference, $converter->getExtReference($url));
    }

    /**
     * @return array
     */
    public function getExtReferenceProvider()
    {
        return [
            [
                'http://localhost/core/app/test',
                ExtReference::create('App', 'test'),
            ],
            [
                '/core/app/test',
                ExtReference::create('App', 'test'),
            ],
            [
                'http://localhost/hans/showcase/blah',
                ExtReference::create('ShowCase', 'blah'),
            ],
        ];
    }

    /**
     * @dataProvider getUrlProvider
     *
     * @param ExtReference $extReference extref object
     * @param string       $routeId      name of route that should get loaded
     * @param string       $url          url we expect to result from the conversion
     *
     * @return void
     */
    public function testGetUrl(ExtReference $extReference, $routeId, $url)
    {
        $this->router
            ->expects($this->once())
            ->method('generate')
            ->with(
                $routeId,
                ['id' => $extReference->getId()]
            )
            ->will($this->returnValue($url));

        $converter = new ExtReferenceConverter(
            $this->router
        );
        $this->assertEquals($url, $converter->getUrl($extReference));
    }

    /**
     * @return array
     */
    public function getUrlProvider()
    {
        return [
            [
                ExtReference::create('App', 'test'),
                'App.get',
                'http://localhost/core/app/test',
            ],
            [
                ExtReference::create('Module', 'en'),
                'Module.get',
                'http://localhost/core/module/en',
            ],
        ];
    }
}
