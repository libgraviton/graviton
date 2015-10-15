<?php
/**
 * ExtReferenceConverterTest class file
 */

namespace Graviton\DocumentBundle\Tests\Service;

use Graviton\DocumentBundle\Entity\ExtReference;
use Graviton\DocumentBundle\Service\ExtReferenceConverter;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

/**
 * ExtReferenceConverter test
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/GPL GPL
 * @link     http://swisscom.ch
 */
class ExtReferenceConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $router;
    /**
     * @var RouteCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collection;
    /**
     * @var Route[]
     */
    private $routes;

    /**
     * setup type we want to test
     *
     * @return void
     */
    public function setUp()
    {
        $this->router = $this->getMockBuilder('\Symfony\Bundle\FrameworkBundle\Routing\Router')
            ->disableOriginalConstructor()
            ->setMethods(['getRouteCollection', 'generate'])
            ->getMock();

        $this->collection = $this->getMockBuilder('\Symfony\Bundle\FrameworkBundle\Routing\RouteCollection')
            ->setMethods(['all'])
            ->getMock();

        $this->routes = [
            new Route(
                '/core/app/{id}',
                [
                    '_controller' => 'graviton.core.controller.app:getAction',
                    '_format' => '~'
                ],
                [
                    '_method' => 'GET',
                    'id' => '[a-zA-Z0-9\-_\/]+',
                ]
            ),
            new Route(
                '/core/app',
                [
                    '_controller' => 'graviton.core.controller.app.appAction',
                    '_format' => '~'
                ],
                [
                    '_method' => 'GET',
                ]
            ),
            new Route(
                '/i18n/language/{id}',
                [
                    '_controller' => 'graviton.i18n.controller.language:getAction',
                    '_format' => '~'
                ],
                [
                    '_method' => 'GET',
                    'id' => '[a-zA-Z0-9\-_\/]+',
                ]
            ),
            new Route(
                '/hans/showcase/{id}',
                [
                    '_controller' => 'gravitondyn.showcase.controller.showcase:getAction',
                    '_format' => '~'
                ],
                [
                    '_method' => 'GET',
                    'id' => '[a-zA-Z0-9\-_\/]+',
                ]
            ),
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
            ->method('getRouteCollection')
            ->will($this->returnValue($this->collection));

        $this->collection
            ->expects($this->once())
            ->method('all')
            ->will($this->returnValue($this->routes));

        $converter = new ExtReferenceConverter(
            $this->router,
            [
                'App' => 'graviton.core.rest.app',
                'Language' => 'graviton.i18n.rest.language',
                'ShowCase' => 'gravitondyn.showcase.rest.showcase',
            ]
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
            $this->router,
            [
                'App' => 'graviton.core.rest.app',
                'Language' => 'graviton.i18n.rest.language',
                'ShowCase' => 'gravitondyn.showcase.rest.showcase',
            ]
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
                'graviton.core.rest.app.get',
                'http://localhost/core/app/test',
            ],
            [
                ExtReference::create('Language', 'en'),
                'graviton.i18n.rest.language.get',
                'http://localhost/i18n/language/en',
            ],
        ];
    }
}
