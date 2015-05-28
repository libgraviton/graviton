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
class ExtReferenceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $doubles = [];

    /**
     * setup type we want to test
     *
     * @return void
     */
    public function setUp()
    {
        Type::registerType('extref', 'Graviton\DocumentBundle\Types\ExtReference');

        $this->doubles['router'] = $this->getMockBuilder('\Symfony\Bundle\FrameworkBundle\Routing\Router')
            ->disableOriginalConstructor()
            ->setMethods(array('getRouteCollection', 'generate'))
            ->getMock();

        $this->doubles['collection'] = $this->getMockBuilder('\Symfony\Bundle\FrameworkBundle\Routing\RouteCollection')
            ->setMethods(array('all'))
            ->getMock();

        $this->doubles['routes'] = [
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
     * @dataProvider mongoRefFromValueProvider
     *
     * @param string $url      external link to convert
     * @param array  $expected expected mogodb ref
     *
     * @return void
     */
    public function testMongoRefFromValue($url, $expected)
    {
        $this->doubles['router']
            ->expects($this->once())
            ->method('getRouteCollection')
            ->will($this->returnValue($this->doubles['collection']));

        $this->doubles['collection']
            ->expects($this->once())
            ->method('all')
            ->will($this->returnValue($this->doubles['routes']));

        $sut = Type::getType('extref');
        $sut->setRouter($this->doubles['router']);
        $sut->setMapping(
            [
                'App' => 'graviton.core.rest.app.get',
                'Language' => 'graviton.i18n.rest.language.get',
                'ShowCase' => 'gravitondyn.showcase.rest.showcase.get',
            ]
        );

        $result = $sut->convertToDatabaseValue($url);

        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function mongoRefFromValueProvider()
    {
        return [
            ['http://localhost/core/app/test', ['$ref' => 'App', '$id' => 'test']],
            ['/core/app/test', ['$ref' => 'App', '$id' => 'test']],
            ['http://localhost/hans/showcase/blah', ['$ref' => 'ShowCase', '$id' => 'blah']],
        ];
    }

    /**
     * @dataProvider convertToPHPValueProvider
     *
     * @param array  $ref     reference as from mongo
     * @param string $routeId name of route that should get loaded
     * @param string $url     url we expect to result from the conversion
     *
     * @return void
     */
    public function testConvertToPHPValue($ref, $routeId, $url)
    {
        $this->doubles['router']
            ->expects($this->once())
            ->method('generate')
            ->with(
                $this->equalTo($routeId),
                $this->equalTo(array('id' => $ref['$id']))
            )
            ->will($this->returnValue($url));

        $sut = Type::getType('extref');
        $sut->setRouter($this->doubles['router']);

        $this->assertEquals($url, $sut->convertToPHPValue($ref));
    }

    /**
     * @return array
     */
    public function convertToPHPValueProvider()
    {
        return [
            [['$ref' => 'App', '$id' => 'test'], 'graviton.core.rest.app.get', 'http://localhost/core/app/test'],
            [
                ['$ref' => 'Language', '$id' => 'en'],
                'graviton.i18n.rest.language.get',
                'http://localhost/i18n/language/en'
            ],
        ];
    }
}
