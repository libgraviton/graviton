<?php
/**
 * ExtReferenceConverterTest class file
 */

namespace Graviton\DocumentBundle\Tests\Service;

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
     * @dataProvider getDbRefProvider
     *
     * @param string       $url      external link to convert
     * @param array|object $expected expected mogodb ref
     *
     * @return void
     */
    public function testGetDbRef($url, $expected)
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
                'App' => 'graviton.core.rest.app.get',
                'Language' => 'graviton.i18n.rest.language.get',
                'ShowCase' => 'gravitondyn.showcase.rest.showcase.get',
            ]
        );
        $this->assertEquals($expected, $converter->getDbRef($url));
    }

    /**
     * @return array
     */
    public function getDbRefProvider()
    {
        return [
            [
                'http://localhost/core/app/test',
                \MongoDBRef::create('App', 'test'),
            ],
            [
                '/core/app/test',
                \MongoDBRef::create('App', 'test'),
            ],
            [
                'http://localhost/hans/showcase/blah',
                \MongoDBRef::create('ShowCase', 'blah'),
            ],
        ];
    }

    /**
     * @dataProvider getUrlProvider
     *
     * @param array|object $ref     reference as from mongo
     * @param string       $routeId name of route that should get loaded
     * @param string       $url     url we expect to result from the conversion
     *
     * @return void
     */
    public function testGetUrl($ref, $routeId, $url)
    {
        $this->router
            ->expects($this->once())
            ->method('generate')
            ->with(
                $routeId,
                ['id' => is_array($ref) ? $ref['$id'] : $ref->{'$id'}]
            )
            ->will($this->returnValue($url));

        $converter = new ExtReferenceConverter(
            $this->router,
            [
                'App' => 'graviton.core.rest.app.get',
                'Language' => 'graviton.i18n.rest.language.get',
                'ShowCase' => 'gravitondyn.showcase.rest.showcase.get',
            ]
        );
        $this->assertEquals($url, $converter->getUrl($ref));
    }

    /**
     * @return array
     */
    public function getUrlProvider()
    {
        return [
            [
                \MongoDBRef::create('App', 'test'),
                'graviton.core.rest.app.get',
                'http://localhost/core/app/test',
            ],
            [
                \MongoDBRef::create('Language', 'en'),
                'graviton.i18n.rest.language.get',
                'http://localhost/i18n/language/en',
            ],
        ];
    }
}
