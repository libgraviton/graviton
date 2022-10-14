<?php
/**
 * functional test for /core/app
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\CoreBundle\Event\HomepageRenderEvent;
use Graviton\TestBundle\Test\RestTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Basic functional test for /.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class MainControllerTest extends RestTestCase
{
    /**
     * @const vendorized app mime type for data
     */
    const CONTENT_TYPE = 'application/json; charset=UTF-8';
    /**
     * @const corresponding vendorized schema mime type
     */
    const SCHEMA_TYPE = 'application/json; charset=UTF-8';

    /**
     * RQL query is ignored
     *
     * @return void
     */
    public function testRqlIsIgnored()
    {
        $client = static::createRestClient();
        $client->request('GET', '/?invalidrqlquery');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    /**
     * check if version is returned in header
     *
     * @return void
     */
    public function testVersionHeader()
    {
        $client = static::createRestClient();
        $client->request('GET', '/');

        $response = $client->getResponse();
        $this->assertEquals(
            $this->getContainer()->getParameter('graviton.core.version.header'),
            $response->headers->get('X-Version')
        );
    }

    /**
     * check for response contents.
     *
     * @return void
     */
    public function testRequestBody()
    {
        $client = static::createRestClient();
        $client->request('GET', '/');

        $results = $client->getResults();

        $this->assertIsArray($results->services);

        $refName = '$ref';
        $serviceRefs = array_map(
            function ($service) use ($refName) {
                return $service->$refName;
            },
            $results->services
        );
        $this->assertContains('http://localhost/core/app/', $serviceRefs);

        $profiles = array_map(
            function ($service) {
                return $service->profile;
            },
            $results->services
        );
        $this->assertContains('http://localhost/schema/core/app/collection', $profiles);
    }

    /**
     * Verifies the correct behavior of determineServices()
     *
     * @return void
     */
    public function testDetermineServices()
    {
        $services = [
            [
                '$ref'    => 'http://localhost/core/product/',
                'profile' => 'http://localhost/schema/core/product/collection'
            ],
            [
                '$ref'    => 'http://localhost/core/app/',
                'profile' => 'http://localhost/schema/core/app/collection'
            ],
        ];

        $routerDouble = $this->getMockBuilder('\Symfony\Component\Routing\Router')
            ->disableOriginalConstructor()
            ->setMethods(array('generate'))
            ->getMock();
        $routerDouble
            ->expects($this->exactly(4))
            ->method('generate')
            ->with(
                $this->isType('string'),
                $this->isType('array'),
                $this->isType('int')
            )
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue($services[0]['$ref']),
                    $this->returnValue($services[0]['profile']),
                    $this->returnValue($services[1]['$ref']),
                    $this->returnValue($services[1]['profile'])
                )
            );

        $restUtilsDouble = $this->createMock('Graviton\RestBundle\Service\RestUtilsInterface');
        $dispatcherDouble = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $dispatcherDouble->method('dispatch')->will($this->returnValue(new HomepageRenderEvent()));

        $optionRoutes = [
            "graviton.core.rest.app.options"     => $routerDouble,
            "graviton.core.rest.product.options" => $routerDouble,
        ];

        $controller = $this->getMockBuilder('\Graviton\CoreBundle\Controller\MainController')
            ->setConstructorArgs(
                [
                    $routerDouble,
                    $restUtilsDouble,
                    $dispatcherDouble,
                    [],
                    []
                ]
            )->getMock();

        $determineServices = $this->getPrivateClassMethod($controller, 'determineServices');

        $this->assertEquals(
            [
                [
                    '$ref'    => 'http://localhost/core/app/',
                    'profile' => 'http://localhost/schema/core/app/collection'
                ],
                [
                    '$ref'    => 'http://localhost/core/product/',
                    'profile' => 'http://localhost/schema/core/product/collection'
                ],
            ],
            $determineServices->invokeArgs($controller, [$optionRoutes])
        );
    }

    /**
     * @return void
     */
    public function testOptionsResponse()
    {
        $client = static::createRestClient();
        $client->request('OPTIONS', '/');

        $response = $client->getResponse();

        $this->assertStringContainsString(
            'If-None-Match',
            $response->headers->get('Access-Control-Allow-Headers')
        );
    }
}
