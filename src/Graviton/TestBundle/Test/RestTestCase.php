<?php
/**
 * REST test case
 *
 * Contains additional helpers for testing RESTful servers
 */

namespace Graviton\TestBundle\Test;

use Graviton\TestBundle\Client;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST test case
 *
 * Contains additional helpers for testing RESTful servers
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class RestTestCase extends GravitonTestCase
{
    /**
     * Create a REST Client.
     *
     * Creates a regular client first so we can profit from the bootstrapping code
     * in parent::createClient and is otherwise API compatible with said method.
     *
     * @param array $options An array of options to pass to the createKernel class
     * @param array $server  An array of server parameters
     *
     * @return \Graviton\TestBundle\Client A Client instance
     */
    protected static function createRestClient(array $options = array(), array $server = array())
    {
        parent::createClient($options, $server);

        $client = static::$kernel->getContainer()->get('graviton.test.rest.client');
        $client->setServerParameters($server);

        return $client;
    }

    /**
     * load fixtures
     *
     * @return void
     */
    public function setUp()
    {
        $this->loadFixtures(
            array(
                'Graviton\I18nBundle\DataFixtures\MongoDB\LoadLanguageData',
                'Graviton\I18nBundle\DataFixtures\MongoDB\LoadTranslatableData'
            ),
            null,
            'doctrine_mongodb'
        );
    }

    /**
     * test for content type based on classname based mapping
     *
     * @param string   $contentType Expected Content-Type
     * @param Response $response    Response from client
     *
     * @return void
     */
    public function assertResponseContentType($contentType, Response $response)
    {
        $this->assertEquals(
            $contentType,
            $response->headers->get('Content-Type'),
            'Content-Type mismatch in response'
        );
    }

    /**
     * assertion for checking cors headers
     *
     * @param string $methods  methods to check for
     * @param object $response response to load headers from
     *
     * @return void
     */
    public function assertCorsHeaders($methods, $response)
    {
        $this->assertEquals('*', $response->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals($methods, $response->headers->get('Access-Control-Allow-Methods'));
    }

    /**
     * assert that putting a fetched resource fails
     *
     * @param string $url    url
     * @param Client $client client to use
     *
     * @return void
     */
    public function assertPutFails($url, $client)
    {
        $client->request('GET', $url);
        $client->put($url, $client->getResults());

        $response = $client->getResponse();
        $this->assertEquals(405, $response->getStatusCode());
        $this->assertEquals('GET, HEAD, OPTIONS', $response->headers->get('Allow'));
    }

    /**
     * Testing private methods for a class.
     *
     * $class = new YourClass(); or service...
     * $method = $this->getPrivateClassMethod(get_class($class), 'getPrivateFunction');
     * $result = $method->invokeArgs( $this->activityManager, [argument1, argument2, ...]);
     *
     * @param string $className  String name for class, full namespace.
     * @param string $methodName Method name to be used
     * @return \ReflectionMethod
     */
    public function getPrivateClassMethod($className, $methodName)
    {
        $reflector = new \ReflectionClass($className);
        $method = $reflector->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }
}
