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
    //private static $_client;

    /**
     * resets languages
     *
     * @after
     *
     * @return void
     */
    public static function languageReset()
    {
        // clear language cache after each test
        static::createRestClient()->getContainer()->get('graviton.i18n.translator')->removeCachedLanguages();
    }

    /**
     * Create a REST Client.
     *
     * Creates a regular client first so we can profit from the bootstrapping code
     * in parent::createRestClient and is otherwise API compatible with said method.
     *
     * @param array $options An array of options to pass to the createKernel class
     * @param array $server  An array of server parameters
     *
     * @return Client A Client instance
     */
    /*
    protected static function createRestClient(array $options = [], array $server = array()) : Client
    {
        if (null == self::$_client) {
            self::$_client = new Client(parent::createClient($options, $server));
        }

        return self::$_client;
    }*/

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
     * Assert presence of rel=schema in Link header
     *
     * @param string   $schemaUrl schema url
     * @param Response $response  response
     *
     * @return void
     */
    public function assertResponseSchemaRel($schemaUrl, Response $response)
    {
        $this->assertStringContainsString(
            '<'.$schemaUrl.'>; rel="schema"',
            $response->headers->get('Link', ''),
            'Schema Link header item missing'
        );
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
}
