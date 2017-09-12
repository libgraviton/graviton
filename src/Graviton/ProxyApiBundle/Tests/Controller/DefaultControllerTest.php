<?php
/**
 * Test cases for basic coverage for ProxyApi Bundle
 */
namespace Graviton\ProxyApiBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;

use Graviton\TestBundle\Test\RestTestCase;

/**
 * Basic functional test for ProxyApi
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class DefaultControllerTest extends RestTestCase
{
    /**
     * Testing basic functionality
     * Api to Weather station, json response
     * @return void
     */
    public function testIndex()
    {
        $client = static::createClient();

        // Let's get information from the schema
        $client->request('GET', '/proxy/');
        $content = $client->getResponse()->getContent();
        $services = json_decode($content, true);

        $this->assertArrayHasKey('weather', $services, '');
        $this->assertArrayHasKey('weather-by-city', $services, '');
        $this->assertArrayHasKey('weather-swiss', $services, '');
    }
    /**
     * Testing basic functionality
     * Api to Weather station, json response
     * @return void
     */
    public function testProxyWithParams()
    {
        $client = static::createClient();

        // Lets get graviton version, core is or endpoint in test config for weather api
        $client->request('GET', '/proxy/weather/weather?q=London,uk');
        $content = $client->getResponse()->getContent();
        $weather = json_decode($content, true);

        $this->assertArrayHasKey('coord', $weather, '');
        $this->assertArrayHasKey('weather', $weather, '');
        $this->assertArrayHasKey('name', $weather, '');
        $this->assertEquals('London', $weather['name'], '');
    }

    /**
     * Testing basic functionality
     * Api to Weather station, limit query params and convert
     * @return void
     */
    public function testProxyQueryParamsLimit()
    {
        $client = static::createClient();

        // Lets get graviton version, core is or endpoint in test config for weather api
        $client->request('GET', '/proxy/weather-by-city?city=London&country=uk');
        $content = $client->getResponse()->getContent();
        $weather = json_decode($content, true);

        $this->assertArrayHasKey('coord', $weather, '');
        $this->assertArrayHasKey('weather', $weather, '');
        $this->assertArrayHasKey('name', $weather, '');
        $this->assertEquals('London', $weather['name'], '');
    }

    /**
     * Testing basic functionality
     * Api to Weather station, limit query params and convert
     * @return void
     */
    public function testProxyQueryParamsLimitMeteoTest()
    {
        $client = static::createClient();

        // ZURICH Lets get graviton version, core is or endpoint in test config for weather api
        $client->request('GET', '/proxy/weather-swiss/ortswetter?city=Zurich');
        $content = $client->getResponse()->getContent();
        $weather = json_decode($content, true);

        $this->assertArrayHasKey('currentWeather', $weather, '');
        $this->assertArrayHasKey('tt', $weather['currentWeather'], '');
        $this->assertArrayHasKey('prognose', $weather, '');
        $this->assertArrayHasKey('mtLocation', $weather, '');
        $this->assertArrayHasKey('name', $weather['mtLocation'], '');
        $this->assertEquals('Zurich', $weather['mtLocation']['name'], '');


        // BERN Lets get graviton version, core is or endpoint in test config for weather api
        $client->request('GET', '/proxy/weather-swiss/ortswetter?city=Bern');
        $content = $client->getResponse()->getContent();
        $weather = json_decode($content, true);
        
        $this->assertEquals('Bern', $weather['mtLocation']['name'], '');
    }
}
