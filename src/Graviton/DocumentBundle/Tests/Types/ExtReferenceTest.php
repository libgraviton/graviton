<?php
/**
 * verify extref custom type
 */

namespace Graviton\DocumentBundle\Tests\Types;

use Graviton\DocumentBundle\Types\ExtReference;
use Doctrine\ODM\MongoDB\Types\Type;
use Symfony\Component\Routing\Route;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/GPL GPL
 * @link     http://swisscom.ch
 */
class ExtReferenceTest extends \PHPUnit_Framework_Testcase
{
    /**
     * setup type we want to test
     *
     * @return void
     */
    public function setUp()
    {
        Type::registerType('extref', 'Graviton\DocumentBundle\Types\ExtReference');
    }

    /**
     * @expectedException RuntimeException
     *
     * @return void
     */
    public function testExceptWithoutRouter()
    {
        $sut = Type::getType('extref');

        $sut->convertToDatabaseValue('');
    }

    /**
     * verify that we get a mongodbref
     *
     * @dataProvider testMongoRefFromValueProvider
     *
     * @param string $url      external link to convert
     * @param array  $expected expected mogodb ref
     *
     * @return void
     */
    public function testMongoRefFromValue($url, $expected)
    {
        $router = $this->getMockBuilder('\Symfony\Bundle\FrameworkBundle\Routing\Router')
            ->disableOriginalConstructor()
            ->setMethods(array('getRouteCollection'))
            ->getMock();

        $collection = $this->getMockBuilder('\Symfony\Bundle\FrameworkBundle\Routing\RouteCollection')
            ->setMethods(array('all'))
            ->getMock();

        $routes = [
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
        ];

        $router
            ->expects($this->once())
            ->method('getRouteCollection')
            ->will($this->returnValue($collection));

        $collection
            ->expects($this->once())
            ->method('all')
            ->will($this->returnValue($routes));

        $sut = Type::getType('extref');
        $sut->setRouter($router);

        $result = $sut->convertToDatabaseValue($url);

        $this->assertEquals($result, $expected);
    }

    /**
     * @return array
     */
    public function testMongoRefFromValueProvider()
    {
        return [
            ['http://localhost/core/app/test', ['$ref' => 'App', '$id' => 'test']],
            ['/core/app/test', ['$ref' => 'App', '$id' => 'test']],
        ];
    }
}
