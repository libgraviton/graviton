<?php
/**
 * REST test case
 *
 * Contains additional helpers for testing RESTful servers
 */

namespace Graviton\Tests;

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
