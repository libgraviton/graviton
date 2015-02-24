<?php
/**
 * functional test for /core/app
 */

namespace Graviton\CoreBundle\Tests\Controller;

use Graviton\TestBundle\Test\RestTestCase;

/**
 * Basic functional test for /.
 *
 * @category GravitonCoreBundle
 * @package  Graviton
 * @author   Lucas Bickel <lucas.bickel@swisscom.com>
 * @author   Dario Nuevo <Dario.Nuevo@swisscom.com>
 * @author   Manuel Kipfer <manuel.kipfer@swisscom.com>
 * @author   Bastian Feder <bastian.feder@swisscom.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
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
     * check if version is returned in header
     *
     * @return void
     */
    public function testVersionHeader()
    {
        $client = static::createRestClient();
        $client->request('GET', '/');

        $composer = json_decode(file_get_contents(__DIR__ . '/../../../../../composer.json'), true);
        $version = $composer['version'];

        $response = $client->getResponse();

        $this->assertEquals($version, $response->headers->get('X-Version'));
    }

    /**
     * check for app link in header
     *
     * @return void
     */
    public function testAppsLink()
    {
        $client = static::createRestClient();
        $client->request('GET', '/');

        $response = $client->getResponse();

        $this->assertContains(
            '<http://localhost/core/app>; rel="apps"; type="application/json"',
            $response->headers->get('Link')
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

        $this->assertEquals(
            'Please look at the Link headers of this response for further information.',
            $results->message
        );

        $this->assertInternalType('array', $results->services);

        $refName = '$ref';
        $serviceRefs = array_map(
            function ($service) use ($refName) {
                return $service->$refName;
            },
            $results->services
        );
        $this->assertContains('http://localhost/core/app', $serviceRefs);

        $profiles = array_map(
            function ($service) {
                return $service->profile;
            },
            $results->services
        );
        $this->assertContains('http://localhost/schema/core/app/collection', $profiles);
    }

    public function testPrepareLinkHeader()
    {
        $routerDouble = $this->getMockBuilder('\Symfony\Component\Routing\Router')
            ->disableOriginalConstructor()
            ->setMethods(array('generate'))
            ->getMock();
        $routerDouble
            ->expects($this->once())
            ->method('generate')
            ->with(
                $this->equalTo('graviton.core.rest.app.all'),
                $this->isType('array'),
                $this->isType('boolean')
            )
            ->will($this->returnValue("http://localhost/core/app"));

        $controller = $this->getProxyBuilder('\Graviton\CoreBundle\Controller\MainController')
            ->setMethods(array('prepareLinkHeader'))
            ->getProxy();

        $this->assertEquals(
            '<http://localhost/core/app>; rel="apps"; type="application/json"',
            $controller->prepareLinkHeader($routerDouble)
        );
    }

    public function testDetermineServices()
    {
        $services = [
            [
                '$ref'    => "http:\/\/localhost\/core\/product",
                "profile" => "http:\/\/localhost\/schema\/core\/product\/collection"
            ],
            [
                '$ref'    => "http:\/\/localhost\/core\/app",
                "profile" => "http:\/\/localhost\/schema\/core\/app\/collection"
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
                $this->isType('boolean')
            )
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue($services[0]['$ref']),
                    $this->returnValue($services[0]['profile']),
                    $this->returnValue($services[1]['$ref']),
                    $this->returnValue($services[1]['profile'])
                )
            );


        $optionRoutes = [
            "graviton.core.rest.app.options" => $routerDouble,
            "graviton.core.rest.product.options" =>$routerDouble,
        ];

        $controller = $this->getProxyBuilder('\Graviton\CoreBundle\Controller\MainController')
            ->setMethods(array('determineServices'))
            ->getProxy();

        $this->assertEquals(
            [
                [
                    '$ref'    => "http:\/\/localhost\/core\/app",
                    "profile" => "http:\/\/localhost\/schema\/core\/app\/collection"
                ],
                [
                    '$ref'    => "http:\/\/localhost\/core\/product",
                    "profile" => "http:\/\/localhost\/schema\/core\/product\/collection"
                ],
            ],
            $controller->determineServices($routerDouble, $optionRoutes)
        );
}


}
