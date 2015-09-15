<?php
/**
 * ProxyController
 */

namespace Graviton\ProxyBundle\Controller;

use Graviton\ProxyBundle\Service\ApiDefinitionLoader;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Proxy\Proxy;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * general controller for all proxy staff
 *
 * @package Graviton\ProxyBundle\Controller
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class ProxyController
{
    /**
     * @var Proxy
     */
    private $proxy;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var ApiDefinitionLoader
     */
    private $apiLoader;

    /**
     * Constructor
     *
     * @param Proxy               $proxy      proxy
     * @param EngineInterface     $templating twig templating engine
     * @param ApiDefinitionLoader $loader     definition loader
     */
    public function __construct(
        Proxy $proxy,
        EngineInterface $templating,
        ApiDefinitionLoader $loader
    ) {
        $this->proxy = $proxy;
        $this->templating = $templating;
        $this->apiLoader = $loader;
    }

    /**
     * action for routing all requests directly to the third party API
     *
     * @param Request $request request
     *
     * @return \Psr\Http\Message\ResponseInterface|Response
     */
    public function proxyAction(Request $request)
    {
        $scheme = $request->getScheme();
        $api = $this->decideApiAndEnpoint($scheme, $request->getUri());
        $this->apiLoader->setOption(
            array(
                "prefix" => "petstore",
                "uri"    => "http://petstore.swagger.io/v2/swagger.json",
            )
        );

        $url = $this->apiLoader->getEndpoint($api['endpoint'], true);
        $url = $scheme."://".$url;

        $response = null;
        try {
            $newRequest = Request::create(
                $request->getUri(),
                $request->getMethod(),
                array(),
                array(),
                array(),
                array(),
                $request->getContent()
            );
            $newRequest->headers->add($request->headers->all());
            $response = $this->proxy->forward($newRequest)->to($url);
        } catch (ClientException $e) {
            $response = $e->getResponse();
        } catch (ServerException $serverException) {
            $response = $serverException->getResponse();
        }

        return $response;
    }

    /**
     * get schema info
     *
     * @param Request $request request
     *
     * @return Response
     */
    public function schemaAction(Request $request)
    {
        $api = $this->decideApiAndEnpoint($request->getScheme(), $request->getUri());
        $this->apiLoader->setOption(
            array(
                "prefix" => "petstore",
                "uri"    => "http://petstore.swagger.io/v2/swagger.json",
            )
        );
        $schema = $this->apiLoader->getEndpointSchema($api['endpoint']);

        $response = new Response(json_encode($schema), 200);

        return $this->templating->renderResponse(
            'GravitonCoreBundle:Main:index.json.twig',
            array('response' => $response->getContent()),
            $response
        );
    }

    /**
     * get API name and endpoint from the url (third party API)
     *
     * @param string $scheme http or https
     * @param string $url    the url
     *
     * @return array
     */
    protected function decideApiAndEnpoint($scheme, $url)
    {
        $pattern = array(
            "@".$scheme.":\/\/@",
            "@3rdparty\/@",
            "@schema\/@",
            "@\/item$@",
        );
        $url = preg_replace($pattern, '', $url);
        //remove host
        $url = str_replace(substr($url, 0, strpos($url, '/') + 1), '', $url);
        //get api name and endpoint
        $apiName = substr($url, 0, strpos($url, '/'));
        $endpoint = str_replace($apiName, '', $url);

        return array(
            "apiName" => $apiName,
            "endpoint" => $endpoint,
        );
    }
}
