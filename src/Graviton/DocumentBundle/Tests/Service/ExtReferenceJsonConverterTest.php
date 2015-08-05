<?php
/**
 * test generic form builder class
 */

namespace Graviton\DocumentBundle\Tests\Form\Type;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Routing\RouterInterface;
use Graviton\DocumentBundle\Service\ExtReferenceJsonConverter;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ExtReferenceJsonConverterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Request
     */
    private $request;

    /**
     * @var ParameterBag|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestAttrs;

    /**
     * @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $router;

    /**
     * setup type we want to test
     *
     * @return void
     */
    public function setUp()
    {

        $this->router = $this->getMockBuilder('\Symfony\Bundle\FrameworkBundle\Routing\Router')
            ->disableOriginalConstructor()
            ->setMethods(['generate'])
            ->getMock();

        $this->requestAttrs = $this->getMockBuilder('\Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $this->requestAttrs
            ->expects($this->any())
            ->method('get')
            ->with('_route')
            ->willReturn('route.id');

        $this->request = new Request();
        $this->request->attributes = $this->requestAttrs;

    }

    /**
     * @dataProvider testData
     *
     * @param array $input input data
     * @param array $expectedResult expected result
     * @param string $routerUrl
     *
     * @return void
     */
    public function testGetName($input, $expectedResult, $routerUrl)
    {
        $this->router
            ->expects($this->once())
            ->method('generate')
            ->will($this->returnValue($routerUrl));

        $fields = [
            'route.id' => [
                'db_link.$ref',
                'deep.deep.application.$ref',
            ],
        ];

        $converter = new ExtReferenceJsonConverter($this->router, ['App' => 'graviton.core.rest.app.get'], $fields);

        $this->assertEquals(
            $expectedResult,
            $converter->convert($input, 'route.id')
        );
    }

    /**
     * @return array
     */
    public function testData()
    {
        return [
            'simple converting' => [
                [
                    'id' => 100,
                    'db_link' => [
                        '$ref' => json_encode(['$ref' => 'App', '$id' => 'tablet'])
                    ]
                ],
                [
                    'id' => 100,
                    'db_link' => [
                        '$ref' => 'http://localhost/core/app/tablet'
                    ]
                ],
                'http://localhost/core/app/tablet'
            ],
            'deep nested converting' => [
                [
                    'id' => 100,
                    'deep' => [
                        'deep' => [
                            'application' => [
                                '$ref' => json_encode(['$ref' => 'App', '$id' => 'admin'])
                            ]
                        ]
                    ]
                ],
                [
                    'id' => 100,
                    'deep' => [
                        'deep' => [
                            'application' => [
                                '$ref' => 'http://localhost/core/app/admin'
                            ]
                        ]
                    ]
                ],
                'http://localhost/core/app/admin'
            ]
        ];
    }
}
